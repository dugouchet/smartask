<?php

namespace SMARTASK\HomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use SMARTASK\HomeBundle\Entity\Contact;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use SMARTASK\HomeBundle\Entity\Groupe;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use SMARTASK\HomeBundle\Entity\Task;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Time;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class DefaultController extends Controller
{
	public function accueilAction(Request $request){
		return $this->render('SMARTASKHomeBundle:Default:accueil.html.twig');
	}
	
	public function deleteContactAPIAction(Request $request){
		$em =$this->getDoctrine()->getManager();
		$resp= $em->getRepository('SMARTASKHomeBundle:Contact')->find(intval( $request->get('resp_id') ) );
		$listtask = $em->getRepository('SMARTASKHomeBundle:Task')->findBy(array('resp' => $resp));
		foreach ($listtask as $task) {
			$em->remove($task);
		}
		$em->remove($resp);
		$em->flush();
	}
	public function deleteGroupAPIAction(Request $request){
		$em =$this->getDoctrine()->getManager();
		$group= $em->getRepository('SMARTASKHomeBundle:Groupe')->find(intval( $request->get('group_id') ) );
		$listtask = $em->getRepository('SMARTASKHomeBundle:Task')->findBy(array('group' => $group));
		foreach ($listtask as $task) {
			$em->remove($task);
		}
		$em->remove($group);
		$em->flush();
	}
	public function deleteTaskAPIAction(Request $request){
		$em =$this->getDoctrine()->getManager();
		$task= $em->getRepository('SMARTASKHomeBundle:Task')->find(intval( $request->get('task_id') ) );
		$em->remove($task);
		$em->flush();
	}
	public function postContactAction(Request $request)
	{
		$contact = new Contact();
		$contact->setName($request->get('name'));
		$contact->setEmail($request->get('email'));
	
		$em =$this->getDoctrine()->getManager();
		$em->persist($contact);
		$em->flush();
	
		return $contact;
	}
	public function postTaskAction(Request $request)
	{
		$em =$this->getDoctrine()->getManager();
		$groupe = $em->getRepository('SMARTASKHomeBundle:Groupe')->find(intval( $request->get('group_id') ) );
		$resp   = $em->getRepository('SMARTASKHomeBundle:Contact')->find(intval( $request->get('resp_id') ) );
		$date = new \DateTime($request->get('date'));
		$time = new \DateTime($request->get('time'));
		$task = new Task();
		$task->setTitre($request->get('titre'));
		$task->setLocalisation($request->get('localisation'));
		$task->setGroup($groupe);
		$task->setResp($resp);
		$task->setDate($date);
		$task->setTime($time);
		$task->setdescription($request->get('description'));
		$task->setIsalarmeon($request->get('isalarmeon'));
	
		
		$em->persist($task);
		$em->flush();
	
		return $task;
	}
	
	public function postGroupAction(Request $request)
	{
		$group = new Groupe();
		$group->setNom($request->get('nom'));
		$group->setdescription($request->get('description'));
	
		$em =$this->getDoctrine()->getManager();
		$em->persist($group);
		$em->flush();
	
		return $group;
	}
	
	public function getAllContactsAction(Request $request)
	{
	
		$repository = $this->getDoctrine()->getManager()->getRepository('SMARTASKHomeBundle:Contact');
		$listContacts = $repository->findAll();
		$formatted = [];
		foreach ($listContacts as $contact) {
			$formatted[] = [
					'id' => $contact->getId(),
					'name' => $contact->getName(),
					'email' => $contact->getEmail(),
			];
		}
	
		return new JsonResponse($formatted);
	}
	
	public function getAllTasksAction(Request $request)
	{
	
		$repository = $this->getDoctrine()->getManager()->getRepository('SMARTASKHomeBundle:Task');
		$listTasks = $repository->findAll();
		$formatted = [];
		foreach ($listTasks as $task) {
			$formatted[] = [
					'id'  => $task->getId(),
					'name'  => $task->getTitre(),
					'description' => $task->getDescription(),
					'localisation' => $task->getLocalisation(),
					'groupe_id' => $task->getGroup()->getId(),
					'resp_id' => $task->getResp()->getId(),
					'date' => $task->getDate(),
					'time' => $task->getTime(),
					'description' => $task->getDescription(),
					'isalarmeon' => $task->getIsalarmeon(),
			];
		}
	
		return new JsonResponse($formatted);
	}
	
	public function getAllGroupsAction(Request $request)
	{
	
		$repository = $this->getDoctrine()->getManager()->getRepository('SMARTASKHomeBundle:Groupe');
		$listGroups = $repository->findAll();
		$formatted = [];
		foreach ($listGroups as $group) {
			$formatted[] = [
					'id'   =>$group->getId(),
					'name' => $group->getNom(),
					'description' => $group->getDescription(),
			];
		}
	
		return new JsonResponse($formatted);
	}
	public function activityAction(Request $request)
	{
		$task = new Task();	
		$formBuilder = $this->get('form.factory')->createBuilder(FormType::class,$task);		
		$formBuilder
		->add('titre'            , TextType::class)
		->add('localisation'     , TextType::class, array('required' => false))
		->add('group'            , EntityType::class,array(
				'class'    	    =>'SMARTASKHomeBundle:Groupe',
				'choice_label'	=>'nom',
				'multiple'	    =>false,
		))
		->add('resp'            , EntityType::class,array(
				'class'    	    =>'SMARTASKHomeBundle:Contact',
				'choice_label'	=>'name',
				'multiple'	    =>false,
		))
		->add('date'             , DateType::class, array('required' => false))
		->add('time'             , TimeType::class, array('required' => false))
		->add('description'      , TextType::class, array('required' => false))
		->add('isalarmeon'       , IntegerType::class)
		->add('enregistrer'      , SubmitType::class)
		;
		$form = $formBuilder->getForm();
		$repository = $this->getDoctrine()->getManager()->getRepository('SMARTASKHomeBundle:Task');
		$listTasks = $repository->findAll();
		return $this->render('SMARTASKHomeBundle:Default:activity.html.twig',array('form' => $form->createView(), 'listTasks' => $listTasks ));
	}
	
	
	public function taskAction(Request $request)
	{
		$task = new Task();
		$formBuilder = $this->get('form.factory')->createBuilder(FormType::class,$task);
		 
		$formBuilder
		->add('titre'            , TextType::class)
		->add('localisation'     , TextType::class, array('required' => false))
		->add('group'            , EntityType::class,array(
			'class'    	    =>'SMARTASKHomeBundle:Groupe',
			'choice_label'	=>'nom',
			'multiple'	    =>false,
		))
		->add('resp'            , EntityType::class,array(
				'class'    	    =>'SMARTASKHomeBundle:Contact',
				'choice_label'	=>'name',
				'multiple'	    =>false,
		))
		->add('date'             , DateType::class, array('required' => false))
		->add('time'             , TimeType::class, array('required' => false))
		->add('description'      , TextType::class, array('required' => false))
		->add('isalarmeon'       , IntegerType::class)
		->add('enregistrer'      , SubmitType::class)
		;
		 
		$form = $formBuilder->getForm();
	
		if ($request->isMethod('POST')){
			$form->handleRequest($request);
			if($form->isValid()){
				$em =$this->getDoctrine()->getManager();
				$em->persist($task);
				$em->flush();
				unset($task);
				unset($form);
				$task = new Task();
				$formBuilder = $this->get('form.factory')->createBuilder(FormType::class,$task);
				$formBuilder
				->add('titre'            , TextType::class)
				->add('localisation'     , TextType::class, array('required' => false))
				->add('group'            , EntityType::class,array(
						'class'    	    =>'SMARTASKHomeBundle:Groupe',
						'choice_label'	=>'nom',
						'multiple'	    =>false,
				))
				->add('resp'            , EntityType::class,array(
						'class'    	    =>'SMARTASKHomeBundle:Contact',
						'choice_label'	=>'name',
						'multiple'	    =>false,
				))
				->add('date'             , DateType::class, array('required' => false))
				->add('time'             , TimeType::class, array('required' => false))
				->add('description'      , TextType::class, array('required' => false))
				->add('isalarmeon'       , IntegerType::class)
				->add('enregistrer'      , SubmitType::class)
				;
				$form = $formBuilder->getForm();
			}
		}
		$repository = $this->getDoctrine()->getManager()->getRepository('SMARTASKHomeBundle:Task');
		$listTasks = $repository->findAll();
		return $this->render('SMARTASKHomeBundle:Default:task.html.twig',array('form' => $form->createView(),'listTasks' => $listTasks));
	}
	
	
    public function groupAction(Request $request)
    {
    	$group = new Groupe();
    	
    	$formBuilder = $this->get('form.factory')->createBuilder(FormType::class,$group);
    	
    	$formBuilder
    	->add('nom'       , TextType::class)
    	->add('description', TextType::class, array('required' => false))
    	->add('enregistrer', SubmitType::class)
    	;
    	
    	$form = $formBuilder->getForm();
    	 
    	if ($request->isMethod('POST')){
    		$form->handleRequest($request);
    		if($form->isValid()){
    			$em =$this->getDoctrine()->getManager();
    			$em->persist($group);
    			$em->flush();
    			unset($group);
    			unset($form);
    			$group = new Groupe();
    			$formBuilder = $this->get('form.factory')->createBuilder(FormType::class,$group);
    			$formBuilder
    			->add('nom'       , TextType::class)
    			->add('description', TextType::class, array('required' => false))
    			->add('enregistrer', SubmitType::class)
    			;
    			$form = $formBuilder->getForm();
    		}
    	}
    	$repository = $this->getDoctrine()->getManager()->getRepository('SMARTASKHomeBundle:Groupe');
    	$listGroups = $repository->findAll();
        return $this->render('SMARTASKHomeBundle:Default:group.html.twig',array('form' => $form->createView(), 'listGroups' => $listGroups ));
    }
    
    
    
    public function contactAction(Request $request)
    {
    	$contact = new Contact();
    	$formBuilder = $this->get('form.factory')->createBuilder(FormType::class,$contact);
    	$formBuilder
    	->add('name',  TextType::class)
    	->add('email', TextType::class)
    	->add('enregistrer',  SubmitType::class);
    	$form = $formBuilder->getForm();
    	if ($request->isMethod('POST')){
    		$form->handleRequest($request);
    		if($form->isValid()){
    			$em =$this->getDoctrine()->getManager();
    			$em->persist($contact);
    			$em->flush();
    			unset($contact);
    			unset($form);
    			$contact = new Contact();
    			$formBuilder = $this->get('form.factory')->createBuilder(FormType::class,$contact);
    			$formBuilder
    			->add('name',  TextType::class)
    			->add('email', TextType::class)
    			->add('enregistrer',  SubmitType::class)
    			;
    			$form = $formBuilder->getForm();
    		}
    	}
    	
    	$repository = $this->getDoctrine()->getManager()->getRepository('SMARTASKHomeBundle:Contact');
    	$listContacts = $repository->findAll();
    	return $this->render('SMARTASKHomeBundle:Default:contact.html.twig',array('form' => $form->createView(), 'listContacts' => $listContacts ) );
    }
    
    public function deleteContactAction($id){
    	$em =$this->getDoctrine()->getManager();
    	$repository_contact = $em->getRepository('SMARTASKHomeBundle:Contact');
    	$repository_task = $em->getRepository('SMARTASKHomeBundle:Task');
    	$contact = $repository_contact->find($id);
    	$listtask = $repository_task->findBy(
    			array('resp' => $contact));
    	foreach ($listtask as $task) {
    		$em->remove($task);;
    	}
    	
    	$em->remove($contact);
    	$em->flush();
    	$listContacts = $repository_contact->findAll();
    	$contact = new Contact();
    	$formBuilder = $this->get('form.factory')->createBuilder(FormType::class,$contact);
    	$formBuilder
    	->add('name',  TextType::class)
    	->add('email', TextType::class)
    	->add('enregistrer',  SubmitType::class);
    	$form = $formBuilder->getForm();
    	return $this->render('SMARTASKHomeBundle:Default:contact.html.twig',array('form' => $form->createView(), 'listContacts' => $listContacts ) );
    }
    public function deleteGroupAction($id){
    	$em =$this->getDoctrine()->getManager();
    	$repository_group = $em->getRepository('SMARTASKHomeBundle:Groupe');
    	$repository_task = $em->getRepository('SMARTASKHomeBundle:Task');
    	$group = $repository_group->find($id);
    	$listtask = $repository_task->findBy(
    			array('group' => $group));
    	foreach ($listtask as $task) {
    		$em->remove($task);;
    	}
    	$em->remove($group);
    	$em->flush();
    	$listGroups = $repository_group->findAll();
    	$group = new Groupe();
    	$formBuilder = $this->get('form.factory')->createBuilder(FormType::class,$group);
    	$formBuilder
    	->add('nom'       , TextType::class)
    	->add('description', TextType::class, array('required' => false))
    	->add('enregistrer', SubmitType::class);
    	$form = $formBuilder->getForm();
    	return $this->render('SMARTASKHomeBundle:Default:group.html.twig',array('form' => $form->createView(), 'listGroups' => $listGroups ) );
    }
    public function deleteTaskAction($id){
    	$em =$this->getDoctrine()->getManager();
    	$repository_task = $em->getRepository('SMARTASKHomeBundle:Task');
    	$task = $repository_task->find($id);
    	$em->remove($task); 
    	$em->flush();
    	$listTasks = $repository_task->findAll();
    	return $this->render('SMARTASKHomeBundle:Default:activity.html.twig',array('listTasks' => $listTasks));
    }
    
}
