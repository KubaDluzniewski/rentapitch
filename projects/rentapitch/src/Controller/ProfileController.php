<?php

namespace App\Controller;

use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Review;

class ProfileController extends AbstractController
{
    #[Route('/api/user-profile', name: 'api_user_profile', methods: ['GET'])]
    public function getUserProfile(ReservationRepository $reservationRepository): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'User not logged in'], 401);
        }

        $reservations = $reservationRepository->findBy(['user' => $user]);

        $currentReservations = [];
        $pastReservations = [];
        $now = new \DateTime('today');

        foreach ($reservations as $reservation) {
            if ($reservation->getEndTime() < $now) {
                $pastReservations[] = $reservation;
            } else {
                $currentReservations[] = $reservation;
            }
        }

        usort($pastReservations, function ($a, $b) {
            return $b->getStartTime() <=> $a->getStartTime();
        });

        $pastReservations = array_slice($pastReservations, 0, 3);
        $limitedCurrentReservations = array_slice($currentReservations, 0, 3);

        $data = [
            'name' => $user->getName(),
            'surname' => $user->getSurname(),
            'email' => $user->getEmail(),
            'currentReservations' => array_map([$this, 'formatReservation'], $limitedCurrentReservations),
            'pastReservations' => array_map([$this, 'formatReservation'], $pastReservations),
            'allCurrentReservations' => array_map([$this, 'formatReservation'], $currentReservations),
        ];

        return new JsonResponse($data);
    }

    private function formatReservation($reservation): array
    {
        $review = $reservation->getReview();
        return [
            'id' => $reservation->getId(),
            'pitchName' => $reservation->getPitch()->getName(),
            'date' => $reservation->getStartTime()->format('Y-m-d'),
            'time' => $reservation->getStartTime()->format('H:i') . ' - ' . $reservation->getEndTime()->format('H:i'),
            'rating' => $review ? $review->getRating() : null,
        ];
    }
}