<?php
// src/Controller/PitchController.php

namespace App\Controller;

use App\Entity\Pitch;
use App\Entity\PitchType;
use App\Repository\PitchRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class PitchController extends AbstractController
{
    private $pitchRepository;
    private $reservationRepository;

    public function __construct(PitchRepository $pitchRepository, ReservationRepository $reservationRepository)
    {
        $this->pitchRepository = $pitchRepository;
        $this->reservationRepository = $reservationRepository;
    }

    #[Route('/pitches/rent/{id}', name: 'pitch_rent', methods: ['GET'])]
    public function rent(int $id): Response
    {
        $pitch = $this->pitchRepository->find($id);

        if (!$pitch) {
            throw $this->createNotFoundException('Pitch not found.');
        }

        $user = $this->getUser();
        $isOwner = $pitch->getOwner() === $user;
        $reservations = $this->reservationRepository->findBy(['pitch' => $pitch]);

        return $this->render('pitch/rent.html.twig', [
            'pitch' => $pitch,
            'isOwner' => $isOwner,
            'reservations' => $reservations,
        ]);
    }

    #[Route('/pitches/add', name: 'pitch_add', methods: ['GET', 'POST'])]
    public function addPitch(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to add a pitch.');
        }

        $pitch = new Pitch();

        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $location = $request->request->get('location');
            $latitude = (float)$request->request->get('latitude');
            $longitude = (float)$request->request->get('longitude');
            $pricePerHour = (float)$request->request->get('pricePerHour');
            $type = $request->request->get('type');
            if (empty($type)) {
                $type = 'Nie podano';
            }

            $pitchType = PitchType::from($type);
            $pitch->setType($pitchType);
            $user = $this->getUser();

            if (empty($name) || empty($location) || !$latitude || !$longitude || empty($type)) {
                $this->addFlash('error', 'All fields are required.');
            } else {
                $pitch->setName($name)
                    ->setLocation($location)
                    ->setLatitude($latitude)
                    ->setLongitude($longitude)
                    ->setType($pitchType)
                    ->setOwner($user)
                    ->setPricePerHour($pricePerHour);

                $user->addRole('ROLE_OWNER');
                $entityManager->persist($user);
                $entityManager->persist($pitch);
                $entityManager->flush();

                $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
                $tokenStorage->setToken($token);

                $this->addFlash('success', 'Pomyślnie dodano boisko.');
                return $this->redirectToRoute('home');
            }
        }

        return $this->render('pitch/add.html.twig', [
            'pitch' => $pitch,
        ]);
    }
}