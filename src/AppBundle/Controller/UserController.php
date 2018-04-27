<?php
/**
 * Created by PhpStorm.
 * User: frans
 * Date: 18-11-17
 * Time: 11:36
 */

namespace AppBundle\Controller;
use AppBundle\Entity\Student;
use AppBundle\Form\UserUpdateType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Form\UserRegistrationType;
use AppBundle\Entity\User;
use AppBundle\Entity\UserProfile;

class UserController extends Controller {

    public function indexAction(Request $request){
        $users = $this->getDoctrine()
            ->getRepository(User::class)->findAll();
        return $this->render('user/index.html.twig', [
            'users' => $users
        ]);
    }

    public function showAction(Request $request, User $id){
        $user = $this->getDoctrine()
            ->getRepository(User::class)->find($id);
        return $this->render('user/show.html.twig', [
            'user' => $user
        ]);
    }

    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder){

        // 1) build the form
        $user = new User();

        $form = $this->createForm(UserRegistrationType::class, $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
           $student = new Student();
           $user->setStudent($student);
            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            // 4) save the User!
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // ... do any other work - like sending them an email, etc
            // maybe set a "flash" success message for the user

            return $this->redirectToRoute('game_landing');
        }

        return $this->render('user/register.html.twig', [
            'form' => $form->createView()
        ]);

    }

    public function updateAction(Request $request, User $id ,UserPasswordEncoderInterface $passwordEncoder){

        // 1) build the form
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $form = $this->createForm(UserUpdateType::class, $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // 3) Encode the password (you could also do this via Doctrine listener)
            if ($user->getPlainPassword() !== null){
                $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($password);
            }


            // 4) save the User!
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // ... do any other work - like sending them an email, etc
            // maybe set a "flash" success message for the user

            $this->addFlash(
                'success',
                'Gebruikergegevens zijn succesvol opgeslagen'
            );

            return $this->redirectToRoute('user_show', ['id'=> $user->getId()]);

        }

        return $this->render('user/update.html.twig', [
            'form' => $form->createView()
        ]);

    }



}
