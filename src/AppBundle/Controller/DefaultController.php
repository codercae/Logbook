<?php

namespace AppBundle\Controller;

use AppBundle\Entity\driver;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Forecast\Forecast;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        //Wer ist momentan auf See?
        $effective = $em->getRepository('AppBundle:driving_effective')->findOneBy(array('effective_date_to' => null));
        if (empty($effective)){
            $aufsee = 'Keiner';
        }else{
            $aufsee = $effective->getDriver()->getFirstname();
        }

        //Letzter Unfall
        $damage = $em->getRepository('AppBundle:damage')->findBy(array(), array('damage_date'=>'desc'));
        if (!empty($damage)){
            $lastDamage = $damage[0]->getDamageDate();
        }else{
            $lastDamage = null;
        }


        //Letzte Tankung
        $refuel = $em->getRepository('AppBundle:refuel')->findBy(array(), array('refuel_date'=>'desc'));
        if (!empty($refuel)){
            $lastRefuel = $refuel[0]->getRefuelDate();
        }else{
            $lastRefuel = null;
        }

        //Geplant für heute
        $todayPlans = $this->getDoctrine()
            ->getManager()
            ->createQuery('SELECT e FROM AppBundle:driving_plan e WHERE e.plane_date_from >= CURRENT_DATE()')
            ->getResult();

        $plansCount = Count($todayPlans);

        $drivers = $em->getRepository('AppBundle:driver')->findall();
        $statHour = Array();

        foreach ($drivers as $driver){
            if (!empty($driver->getCosts())){
                $costs = $driver->getCosts();
                foreach ($costs as $cost){
                    if ($cost->getYear() == date('Y')){
                        $statHour[$driver->getId()] = ['name' => $driver->getDisplayName(), 'hours' => $cost->getHours()];
                    }
                }
            }
        }

        //Weather forecast
        $forecast = new Forecast('a182fe6731e040598bc8886f756ad6bb');
        $data = $forecast->get('46.9667','8.3333', null, array('lang' => 'de', 'units' => 'auto'));
        $daily = $data->daily->data;
        $dailyWeather = Array();
        $flag = 'X';
        setlocale(LC_TIME, 'de_DE');
        foreach ($daily as $key => $value){
            $dailyWeather[$key]['day'] = strftime('%a',$value->time);
            $dailyWeather[$key]['maxTemp'] = round($value->temperatureMax, 1);
            $dailyWeather[$key]['minTemp'] = round($value->temperatureMin, 1);
            $dailyWeather[$key]['icon'] = $value->icon;
            if ($flag == 'X'){
                $dailyWeather[$key]['class'] = 'bg';
                $flag = '';
            }else{
                $dailyWeather[$key]['class'] = '';
                $flag = 'X';
            }

        }

        $currenlyWeather['day'] = strftime('%A',$data->currently->time);
        $currenlyWeather['icon'] = $data->currently->icon;
        $currenlyWeather['maxTemp'] = round($dailyWeather[0]['maxTemp'], 1);
        $currenlyWeather['minTemp'] = round($dailyWeather[0]['minTemp'], 1);
        $currenlyWeather['currentTemp'] = round($data->currently->temperature, 1);

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'aufsee' => $aufsee,
            'lastDamage' => $lastDamage,
            'lastRefuel' => $lastRefuel,
            'todayPlans' => $plansCount,
            'chartValue' => $statHour,
            'currentWeather' => $currenlyWeather,
            'dailyWeather' => $dailyWeather
        ]);
    }
    /**
     * @Route("/logout", name="security_logout")
     */
    public function logoutAction()
    {
        throw new \Exception('this should not be reached!');
    }

    /**
     * @Route("/auswertung/{year}", name="auswertung_show")
     */
    public function auswertungShow($year)
    {
        $em = $this->getDoctrine()->getManager();

        $drivers = $em->getRepository('AppBundle:driver')->findall();
        $currentYear = Array();
        $totalRefuel = 0;
        $totalHours = 0;
        $durchschnitt = 0;
        $hexRed = '#FF0000';
        $hexGreen = '#55AA55';

        foreach ($drivers as $driver){
            if (!empty($driver->getCosts())){
                $costs = $driver->getCosts();
                foreach ($costs as $cost){
                    if ($cost->getYear() == $year){
                        $currentYear[$driver->getId()] = ['name' => $driver->getDisplayName(), 'refuelcost' => $cost->getCredit() , 'hours' => $cost->getHours()];
                        $totalRefuel += $cost->getCredit();
                        $totalHours += $cost->getHours();
                    }
                }
            }
        }

        // Division by Zero
        if ($totalHours > 0){
            $durchschnitt = round((float)$totalRefuel / (float)$totalHours, 2);
        }

        foreach ($currentYear as $key => $value){
            $depit = round($value['refuelcost'] - $durchschnitt * $value['hours'], 2);
            $currentYear[$key]['depit'] = $depit;
            if ($depit > 0){
                $currentYear[$key]['color'] = $hexGreen;
            }else{
                $currentYear[$key]['color'] = $hexRed;
            }
        }

        return $this->render('default/auswertung.html.twig', array(
            'year' => $year,
            'list' => $currentYear,
            'totalRefuel' => $totalRefuel,
            'totalHours' => $totalHours,
            'durchschnitt' => $durchschnitt
        ));
    }

    /**
     * @Route("/user/{id}", name="user_edit")
     * @param driver $driver
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function userEdit(driver $driver, Request $request)
    {

        $form = $this->createForm('AppBundle\Form\UserType', $driver);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //EntityManager definieren
            $em = $this->getDoctrine()->getManager();

            $displayName = $driver->getFirstname() . ' ' . $driver->getName();
            $driver->setDisplayName($displayName);

            $plainPassword = $form["password"]->getData();

            if (!empty($plainPassword)){
                $encoder = $this->container->get('security.password_encoder');
                $encoded = $encoder->encodePassword($driver, $plainPassword);

                $driver->setPassword($encoded);
            }

            //Abo speichern
            $em->persist($driver);
            //Save
            $em->flush();


            return $this->redirectToRoute('homepage');
        }
        return $this->render('User/userEdit.html.twig', array(
            'user' => $driver,
            'edit_form' => $form->createView()
        ));
    }
}
