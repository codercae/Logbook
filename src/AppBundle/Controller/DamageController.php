<?php
/**
 * Created by PhpStorm.
 * User: CoderCAE
 * Date: 03.03.2017
 * Time: 15:57
 */
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use \AppBundle\Entity\damage;

class DamageController extends Controller
{
    /**
     * @Route("/damage", name="damage_show")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function damageShow()
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:damage');
        $damage = $repository->findAll();
        // replace this example code with whatever you need
        return $this->render('damage/damageShow.html.twig', array(
            'damages' => $damage
        ));
    }

    /**
     * @Route("/damage/create", name="damage_create")
     * @Route("/damage/edit/{id}", name="damage_edit")
     * @param Request $request
     * @param damage $damage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function damageCreate(Request $request, damage $damage = null)
    {
        if ($damage == null) {
            $damage = new damage();
        }

        $form = $this->createForm('AppBundle\Form\DamageType', $damage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //EntityManager definieren
            $em = $this->getDoctrine()->getManager();

            // Logged User
            $damage->setDriver($this->getUser());

            //Refuel speichern
            $em->persist($damage);
            //Save
            $em->flush();

            $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
            $path = $helper->asset($damage, 'imageFile');

            $message = \Swift_Message::newInstance()
                ->setSubject('Unfall: ' . $damage->getDriver()->getDisplayName() . ', ' . $damage->getBoat()->getFullname())
                ->setTo('hubert.buehlmann@garage-flury.ch')
                ->setBody('<p><span style="font-size: 15pt;"><strong>Unfall im Logbuch</strong></span></p>
                           <p>Datum: '.$damage->getDamageDate()->format('d.m.Y').'</p>
                           <p>Fahrer: '.$damage->getDriver()->getDisplayName().'</p>
                           <p>Boot: '.$damage->getBoat()->getFullname().'</p>
                           <p>Unfallort: '.$damage->getPlace().'</p>
                           <p>Beschreibung: '.$damage->getDescription().'</p>
                           <p><a href="http://'.$_SERVER['HTTP_HOST'].$path.'" target="_blank">Unfallfoto</a></p>',
                    'text/html'
                    );
            $this->get('mailer')->send($message);

            return $this->redirectToRoute('damage_show');
        }
        // replace this example code with whatever you need
        return $this->render('damage/damageCreate.html.twig', array(
            'damage' => $damage,
            'edit_form' => $form->createView()
        ));
    }

    /**
     * @Route("/damage/delete/{id}", name="damage_delete")
     * @param damage $damage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function damageDelete(damage $damage)
    {
        //EntityManager definieren
        $em = $this->getDoctrine()->getManager();
        $em->remove($damage);
        $em->flush();
        // replace this example code with whatever you need
        return $this->redirectToRoute('damage_show');
    }

    /**
     * @Route("/damage/detail/{id}", name="damage_detail")
     * @param damage $damage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function damageDetail(damage $damage)
    {
        // replace this example code with whatever you need
        return $this->render('damage/damageDetail.html.twig', array(
            'damage' => $damage
        ));
    }
}
