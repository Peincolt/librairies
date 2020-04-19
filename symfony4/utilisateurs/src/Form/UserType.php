<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class UserType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username',null,[
                'label' => 'Nom d\'utilisateur'
            ])
            ->add('email', EmailType::class)
        ;

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function(FormEvent $event) {
                $user = $event->getData();
                $form = $event->getForm();
                $this->setFields($form, $user);
            }
        );
    }

    private function setFields($form, $user)
    {
        if (empty($user->getId())) {
            $form
                ->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'invalid_message' => 'Les deux mots de passe doivent être identiques',
                    'required' => true,
                    'first_options' => ['label' => 'Saisissez votre mot de passe'],
                    'second_options' => ['label' => 'Confirmer votre mot de passe']
                ])
                ->add('roles', ChoiceType::class, [
                    'choices' => [
                        'Utilisateur' => 'ROLE_USER',
                        'Administrateur' => 'ROLE_ADMIN'
                    ],
                    'label' => 'Type de compte',
                    'multiple' => true,
                    'expanded' => true,
                    'required' => true
                ]);
        } else {
            $form
                ->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'invalid_message' => 'Les deux mots de passe doivent être identiques',
                    'required' => false,
                    'mapped' => false,
                    'first_options' => ['label' => 'Saisissez votre mot de passe'],
                    'second_options' => ['label' => 'Confirmer votre mot de passe']
                ]);

            if ($this->security->isGranted('ROLE_ADMIN')) {
                $form->add('roles', ChoiceType::class, [
                    'choices' => [
                        'User' => 'ROLE_USER',
                        'Admin' => 'ROLE_ADMIN'
                    ],
                    'label' => 'Type de compte',
                    'multiple' => true,
                    'expanded' => true
                ]);
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
