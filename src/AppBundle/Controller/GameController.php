<?php
/**
 * Created by PhpStorm.
 * User: frans
 * Date: 01-12-17
 * Time: 16:44
 */

namespace AppBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class GameController extends Controller{

    public function landingAction(Request $request){
        return $this->render('game/landing.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }


}