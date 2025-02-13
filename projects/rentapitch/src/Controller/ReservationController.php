<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\PitchRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ReservationController extends AbstractController
{
    private ReservationRepository $reservationRepository;
    private SerializerInterface $serializer;

    public function __construct(ReservationRepository $reservationRepository, SerializerInterface $serializer)
    {
        $this->reservationRepository = $reservationRepository;
        $this->serializer = $serializer;
    }

    #[Route('/reservation/create', name: 'reservation_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, PitchRepository $pitchRepository): Response
    {
        $timezone = new \DateTimeZone('Europe/Warsaw');
        $start = new \DateTime($request->request->get('start'), $timezone);
        $end = new \DateTime($request->request->get('end'), $timezone);
        $pitchId = $request->request->get('pitch_id');

        $pitch = $pitchRepository->find($pitchId);

        if (!$pitch) {
            return $this->render('404.html.twig', [], new Response('', 404));
        }

        $user = $this->getUser(); // Get the currently logged-in user

        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Musisz być zalogowany aby utworzyć rezerwację'], Response::HTTP_FORBIDDEN);
        }

        $now = new \DateTime('now', $timezone);
        if ($start < $now) {
            return new JsonResponse(['success' => false, 'message' => 'Przepraszamy nie da się utworzyć rezerwacji na datę przeszłą'], Response::HTTP_BAD_REQUEST);
        }

        // Check for overlapping reservations
        $existingReservations = $this->reservationRepository->createQueryBuilder('r')
            ->where('r.pitch = :pitch')
            ->andWhere('r.startTime < :end')
            ->andWhere('r.endTime > :start')
            ->setParameter('pitch', $pitch)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();

        if (count($existingReservations) > 0) {
            return new JsonResponse(['success' => false, 'message' => 'Przepraszamy, już istnieje rezerwacja na dany termin'], Response::HTTP_CONFLICT);
        }

        $reservation = new Reservation();
        $reservation->setStartTime($start);
        $reservation->setEndTime($end);
        $reservation->setPitch($pitch);
        $reservation->setUser($user);

        $em->persist($reservation);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/api/reservations/pitch/{pitchId}', name: 'api_reservations_pitch', methods: ['GET'])]
    public function getReservationsByPitch(int $pitchId): Response
    {
        $reservations = $this->reservationRepository->findBy(['pitch' => $pitchId]);
        if (!$reservations) {
            return $this->render('404.html.twig', [], new Response('', 404));
        }
        $events = [];
        foreach ($reservations as $reservation) {
            $user = $reservation->getUser();
            $ownerId = $reservation->getPitch()->getOwner()->getId(); // Pobranie ID właściciela

            $events[] = [
                'title' => $reservation->getUser()->getFirstname() . ' ' . $reservation->getUser()->getLastname(),
                'start' => $reservation->getStartTime()->format('c'),
                'end' => $reservation->getEndTime()->format('c'),
                'extendedProps' => [
                    'user' => [
                        'firstName' => $user->getFirstName(),
                        'lastName' => $user->getLastName(),
                    ],
                    'ownerId' => $ownerId, // Dodanie właściciela
                ],
            ];
        }

        return new JsonResponse($events);
    }

    #[Route('/api/reservations/{id}/rate', name: 'rate_reservation', methods: ['POST'])]
    public function rateReservation(Request $request, int $id, ReservationRepository $reservationRepository, EntityManagerInterface $entityManager): Response
    {
        $reservation = $reservationRepository->find($id);

        if (!$reservation) {
            return $this->render('404.html.twig', [], new Response('', 404));
        }

        $data = json_decode($request->getContent(), true);
        $rating = $data['rating'] ?? null;

        if ($rating < 1 || $rating > 5) {
            return new JsonResponse(['message' => 'Ocena musi być w zakresie od 1 do 5.'], 400);
        }

        $reservation->setRating($rating);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Dziękujemy za ocenę!']);
    }

    #[Route('/api/reservations/{id}/delete', name: 'delete_reservation', methods: ['DELETE'])]
    public function deleteReservation(int $id, ReservationRepository $reservationRepository, EntityManagerInterface $entityManager): Response
    {
        $reservation = $reservationRepository->find($id);

        if (!$reservation) {
            return $this->render('404.html.twig', [], new Response('', 404));
        }

        $entityManager->remove($reservation);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Rezerwacja została usunięta.']);
    }
    #[Route('/reservation/{id}', name: 'reservation_show', methods: ['GET'])]
    public function show(int $id, ReservationRepository $reservationRepository): Response
    {
        $reservation = $reservationRepository->find($id);

        if (!$reservation) {
            return $this->render('404.html.twig', [], new Response('', 404));
        }

        $user = $this->getUser();
        $isOwner = $reservation->getPitch()->getOwner() === $user;

        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
            'isOwner' => $isOwner,
        ]);
    }


}