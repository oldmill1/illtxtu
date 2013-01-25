<?php

namespace Apple\DiasporaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Apple\DiasporaBundle\Entity\Reminder;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;

use Doctrine\ORM\Query\ResultSetMapping;

class DefaultController extends Controller
{
    public function indexAction()
    {
    	$response = $this->forward('AppleDiasporaBundle:Default:new');
    	return $response;
    }

    protected function includeTwilio()
    {
      require('../vendor/twilio-php/Services/Twilio.php');
    }

    public function newAction(Request $request)
    { 
    	$reminder = new Reminder(); 

    	$form = $this->createFormBuilder($reminder)
    		->add('task', 'textarea')
    		->add('pulltime', 'text')
    		->add('phonenumber', 'text')
    		->getForm(); 

        if ($request->isMethod('POST')){ 
            $data = $request->request->get('form');
            $task = $data['task']; 
            $phonenumber = $data['phonenumber']; 
            $pulltime = $data['pulltime']; 
            $timetime = $data['timetime'];
            $date = array(); 
            $date['month']  = substr($pulltime, 0, 2); 
            $date['day']    = substr($pulltime, 3, 2);  
            $date['year']   = substr($pulltime, 6, 4);
            $date['hour']   = date("H:i", strtotime($timetime));
            $datetime = $date['year'] . "-" . $date['month'] . "-" . $date['day'] . " " . $date['hour']; 
            $date = new \DateTime($datetime);
            $reminder->setPulltime($date);
            $reminder->setTask($task); 
            $reminder->setPhonenumber($phonenumber);
            $reminder->setDonation(0);
            $reminder->setCreatedAt(new \DateTime());
            $reminder->setUpdatedAt(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($reminder);
            $em->flush();
            return $this->render('AppleDiasporaBundle:Default:success.html.twig', array('reminder'=>$reminder));
        }

        return $this->render('AppleDiasporaBundle:Default:new.html.twig', array('form'=>$form->createView()));
    }

    public function checkAction()
    {  
        $this->includeTwilio();
        $repository = $this->getDoctrine()->getRepository('AppleDiasporaBundle:Reminder');
        $query = $repository->createQueryBuilder('r')
                    ->orderBy('r.pulltime', 'ASC')
                    ->getQuery();
        $reminders = $query->getResult(); 
        foreach ( $reminders as $reminder ) 
        { 
            $pulltime = $reminder->getPulltime(); 
            $now = new \DateTime(); 
            if ( $now >= $pulltime ) {
                $sid = "ACb6dda79929814208e4fb64242a03a3cb";
                $token = "0197e2454b7edd115b103a6c60317734";
                $client = new \Services_Twilio($sid, $token);
                $f_phonenumber = "+1" . str_replace(" ", "", $reminder->getPhonenumber());
                
                $sms_message_outgoing = $client->account->sms_messages->create(
                    "+16476943470", // From
                    $f_phonenumber, // To 
                    $reminder->getTask()
                );
                $em = $this->getDoctrine()->getEntityManager();
                $delete = $repository->find($reminder->getId());
                $em->remove($delete);
                $em->flush();
            } 
        }
        return new Response(); 
    }

}































