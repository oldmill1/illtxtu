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

    public function processReplyAction(Request $request)
    { 
        $sender = $request->request->get('From');
        $body = $request->request->get('Body');
        
        $sender = "+16473781318";
        $body = "send me stuff, 134"; 

        $values = explode(",", $body); 
        $values_size = count($values);
        if ($values_size==1)
        { 
            // provided too few data points
        }
        elseif ($values_size==2||$values_size==3)
        { 
            $task= strip_tags(trim($values[0]));
            $time= trim($values[1]); 
            $dateIntervalString = "PT"; 
            if ( substr($time, 0, 2) == 'in' ){
                $inString = trim(str_replace("in", "", $time));
                $dArray = explode(" ", $inString);
                foreach ($dArray as $key => $link) {
                    if ($dArray[$key] == ''){ unset($dArray[$key]); }
                }
                if (preg_match('/[a]/', $dArray[0])) {
                    $dateIntervalString = $dateIntervalString . "1"; 
                } elseif (preg_match('/[0-9]/', $dArray[0]))  {
                    $dateIntervalString = $dateIntervalString . $dArray[0] . "H"; 
                }
                if (count($dArray) > 2) { 
                    $dateIntervalString =  $dateIntervalString . $dArray[2] . "M"; 
                }
                $date = new \DateTime(); 
                $date->add(new \DateInterval($dateIntervalString)); 
            } elseif(preg_match('/[Ll]ater [Tt]oday/', $time)) {
                $date = new \DateTime();
                $date->add(new \DateInterval("PT6H"));
            } else {
                $timeChunks = str_split($time); 
                foreach ($timeChunks as $chunkKey => $chunkValue ) { 
                    if (preg_match('/[a-z]/', $chunkValue)){
                        $suffix[] = $chunkValue;
                        unset($timeChunks[$chunkKey]); 
                    } 
                } 
                while (end($timeChunks)==' '){
                    array_pop($timeChunks);
                }
                $timeString = implode($timeChunks); 
                if(!preg_match('/[:]/', $timeString)){
                    if (strlen($timeString) == 3) {
                        $timeString = substr($timeString, 0, 1) . ":" . substr($timeString, 1);
                    }elseif( strlen($timeString) == 4)  {
                        $timeString = substr($timeString, 0, 2) . ":" . substr($timeString, 2);
                    }
                }
                if ( $timeChunks[0]!='0' ) {                
                    $timeString = "0" . $timeString; 
                }
                if (strlen($timeString)>5){
                    $timeString = substr($timeString, 1);
                }
                if (isset($suffix) && count($suffix)>0){
                    if ($suffix[0]=='a'){
                        $timeString = $timeString . " AM"; 
                    } else {
                        $timeString = $timeString . " PM"; 
                    }
                } else {
                    // not found, assume pm 
                    $timeString = $timeString . " PM"; 
                }
                $timeString = str_replace(" ", "", $timeString);
                $timeString = str_replace("PM", " PM", $timeString);
                $timeString = str_replace("AM", " AM", $timeString);
                $timeString = date("H:i", strtotime($timeString)); 
                if ($values_size==2 ) {
                    $dateString = date("Y-m-d"); 
                } elseif($values_size==3)  {
                    $dateString = date("Y-m-d", strtotime($values[2])); 
                }   
                $dateTimeString = $dateString . " " . $timeString; 
                $date = new \DateTime($dateTimeString);
            }
        }

        $reminder = new Reminder(); 
        $reminder->setTask($task);
        $reminder->setPhonenumber($sender);
        $reminder->setCreatedAt(new \DateTime());
        $reminder->setUpdatedAt(new \DateTime());
        $reminder->setDonation(0);
        $reminder->setPulltime($date);

        $em = $this->getDoctrine()->getManager();
        $em->persist($reminder);
        $em->flush();

        return new Response($reminder->getId()); 
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































