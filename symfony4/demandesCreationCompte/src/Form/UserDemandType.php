<?php

namespace App\Form;

use App\Entity\UserDemand;
use App\Service\Entity\Guild as GuildHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserDemandType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username',TextType::class,[
                'label' => 'Nom d\'utilisateur',
                'required' => true
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les deux mots de passe doivent être identiques',
                'required' => true,
                'first_options' => ['label' => 'Saisissez votre mot de passe'],
                'second_options' => ['label' => 'Confirmer votre mot de passe']
            ])
            ->add('email', EmailType::class)
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Membre' => 'USER',
                    'Officier' => 'ADMIN'
                ],
                'label' => 'Quel est votre statut dans la guilde ?'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserDemand::class,
        ]);
    }
}
