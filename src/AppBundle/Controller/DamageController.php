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
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function damageShow(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('damage/damageShow.html.twig');
    }

    /**
     * @Route("/damage/create", name="damage_create")
     * @param damage $damage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function damageCreate(damage $damage)
    {
        // replace this example code with whatever you need
        return $this->render('damage/damageCreate.html.twig');
    }

    /**
     * @Route("/damage/delete/{id}", name="damage_delete")
     * @param damage $damage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function damageDelete(damage $damage)
    {
        // replace this example code with whatever you need
        return $this->render('damage/damageShow.html.twig');
    }

    /**
     * @Route("/damage/edit/{id}", name="damage_edit")
     * @param damage $damage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function damageEdit(damage $damage)
    {
        // replace this example code with whatever you need
        return $this->render('damage/damageEdit.html.twig');
    }

    /**
     * @Route("/damage/detail/{id}", name="damage_detail")
     * @param damage $damage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function damageDetail(damage $damage)
    {
        // replace this example code with whatever you need
        return $this->render('damage/damageDetail.html.twig');
    }
}