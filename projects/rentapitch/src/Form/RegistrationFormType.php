<?php
// src/Form/RegistrationFormType.php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Proszę podać email',
                    ]),
                    new Email([
                        'message' => 'Email "{{ value }}" jest nieprawidłowy.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Hasło',
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Proszę podać hasło',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Hasło powinno mieć co najmniej {{ limit }} znaków',
                        'max' => 64,
                    ]),
                ],
            ])
            ->add('confirmPassword', PasswordType::class, [
                'label' => 'Potwierdź hasło',
                'mapped' => false,
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Imię',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Proszę podać imię',
                    ]),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nazwisko',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Proszę podać nazwisko',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}