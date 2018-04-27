<?php
/**
 * Created by PhpStorm.
 * User: frans
 * Date: 01-12-17
 * Time: 16:44
 */

namespace AppBundle\Controller;
use AppBundle\Entity\Word;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\HttpFoundation\JsonResponse;

class WordController extends Controller{

    public function importAction(Request $request){
        set_time_limit(0);
        $entries = file("../engfreq.txt");
        $em = $this->getDoctrine()->getManager();
        foreach ($entries as $entry) {
            $wordinfo = preg_split("/\s+/", $entry);
            $word = new Word($wordinfo[0], $wordinfo[1]);
            $em->persist($word);
            $em->flush();
            unset($word);
            unset($wordinfo);
        }

        $this->addFlash(
            'success',
            'Importeren geslaagd'
        );

        return $this->redirectToRoute('game_landing');
    }

}