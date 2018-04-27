<?php
/**
 * Created by PhpStorm.
 * User: frans
 * Date: 03-12-17
 * Time: 14:03
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\User;

class UserRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder
            ->add('email', EmailType::class, array(
                'attr' =>
                    array(
                        'placeholder' => 'email',
                        'class' => 'input')))
            ->add('username', TextType::class, array(
                'attr' =>
                    array(
                        'placeholder' => 'gebruikersnaam',
                        'class' => 'input')))
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => array(
                    'attr' => array(
                        'placeholder' => 'wachtwoord',
                        'class' => 'input')
                    ),
                'second_options' => array(
                    'attr' => array(
                        'placeholder' => 'wachtwoord (herhaal)',
                        'class' => 'input')
                )
            ])
            ->add('user_profile',  UserProfileType::class);
    }

    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => array("Default"),
            'cascade_validation' => true,
            'cascade_persist' => true
        ]);
    }
}
