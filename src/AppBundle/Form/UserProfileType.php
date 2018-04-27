<?php
/**
 * Created by PhpStorm.
 * User: frans
 * Date: 03-12-17
 * Time: 14:03
 */

namespace AppBundle\Form;

use AppBundle\Entity\UserProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\User;

class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder
            ->add('first_name', TextType::class, array(
                'attr' =>
                    array(
                        'placeholder' => 'voornaam',
                        'class' => 'input')))
            ->add('last_name', TextType::class, array(
                'attr' =>
                    array(
                        'placeholder' => 'achternaam',
                        'class' => 'input')))
            ->add('dob', TextType::class, array(
                'attr' =>
                    array(
                        'placeholder' => 'geboortedatum',
                        'class' => 'input datepicker')))
            ->add('gender', ChoiceType::class, array(
                'attr' =>
                    array(
                        'placeholder' => 'geboortedatum',
                        'class' => 'select'),
                'choices' =>
                    array(
                        'man' => 0,
                        'vrouw' => 1,
                        'ik denk niet in hokjes' => 2
                    )
            ));
    }

    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults([
            'data_class' => UserProfile::class,
            'cascade_persist' => true
        ]);
    }
}
