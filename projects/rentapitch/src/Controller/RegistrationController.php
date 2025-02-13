<?php
// src/Controller/RegistrationController.php

namespace App\Controller;

use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $plainPassword = $form->get('plainPassword')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();

            if ($plainPassword !== $confirmPassword) {
                $this->addFlash('error', 'Hasła nie są zgodne.');
                return $this->redirectToRoute('app_register');
            }

            $existingUser = $this->entityManager
                ->getRepository(User::class)
                ->findOneBy(['email' => $email]);

            if ($existingUser) {
                $this->addFlash('error', 'Konto z tym adresem e-mail już istnieje. Spróbuj się zalogować.');
                return $this->redirectToRoute('app_login');
            }
            
            $user = new User();
            $user->setEmail($email);
            $user->setFirstName($form->get('firstName')->getData());
            $user->setLastName($form->get('lastName')->getData());
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($passwordHasher->hashPassword(
                $user,
                $plainPassword
            ));

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Konto zostało utworzone. Zaloguj się.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}