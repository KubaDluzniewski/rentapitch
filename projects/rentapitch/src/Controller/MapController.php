<?php

namespace App\Controller;

use App\Entity\Review;
use App\Repository\PitchRepository;
use App\Repository\ReservationRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MapController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index()
    {
        return $this->render('map/index.html.twig');
    }

    #[Route('/api/pitches', name: 'api_pitches')]
    public function getPitches(PitchRepository $repository): JsonResponse
    {
        $pitches = $repository->findAll();

        $data = array_map(function ($pitch) {
            $reviews = array_map(function ($review) {
                return [
                    'rating' => $review->getRating(),
                ];
            }, $pitch->getReviews()->toArray());

            return [
                'id' => $pitch->getId(),
                'name' => $pitch->getName(),
                'lat' => $pitch->getLatitude(),
                'lng' => $pitch->getLongitude(),
                'type' => $pitch->getType(),
                'pricePerHour' => $pitch->getPricePerHour(),
                'reviews' => $reviews,
            ];
        }, $pitches);

        return new JsonResponse($data);
    }

    #[Route('/api/reservations/{id}/rate', name: 'reservation_rate', methods: ['POST'])]
    public function rate(int $id, Request $request, EntityManagerInterface $em, ReservationRepository $reservationRepository, ReviewRepository $reviewRepository): JsonResponse
    {
        $reservation = $reservationRepository->find($id);

        if (!$reservation) {
            return new JsonResponse(['success' => false, 'message' => 'Reservation not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $rating = $data['rating'] ?? null;

        if ($rating === null || $rating < 1 || $rating > 5) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid rating value'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->getUser();
        $existingReview = $reviewRepository->findOneBy(['user' => $user, 'pitch' => $reservation->getPitch(), 'reservation' => $reservation]);

        if ($existingReview) {
            $existingReview->setRating($rating);
            $em->flush();
            return new JsonResponse(['success' => true, 'message' => 'Review updated successfully']);
        }

        $review = new Review();
        $review->setUser($user);
        $review->setPitch($reservation->getPitch());
        $review->setReservation($reservation);
        $review->setRating($rating);

        $em->persist($review);
        $em->flush();

        return new JsonResponse(['success' => true, 'message' => 'Review added successfully']);
    }

    #[Route('/api/change-password', name: 'api_change_password', methods: ['POST'])]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        $newPassword = $data['newPassword'] ?? null;

        if (!$newPassword) {
            return new JsonResponse(['success' => false, 'message' => 'New password is required'], Response::HTTP_BAD_REQUEST);
        }

        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
        $em->flush();

        return new JsonResponse(['success' => true, 'message' => 'Password changed successfully']);
    }


}