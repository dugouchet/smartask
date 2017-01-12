<?php

namespace SMARTASK\APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use SMARTASK\HomeBundle\Entity\Contact;
use SMARTASK\HomeBundle\Entity\Task;
use SMARTASK\HomeBundle\Entity\Groupe;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
	public function deleteContactAPIAction(Request $request)//API Method
	{//pas besoin d'utilisateur car la tache a un id unique
	$em =$this->getDoctrine()->getManager();
	$resp= $em->getRepository('SMARTASKHomeBundle:Contact')->find(intval( $request->get('resp_id') ) );
	$listtask = $em->getRepository('SMARTASKHomeBundle:Task')->findBy(array('resp' => $resp));
	foreach ($listtask as $task) {
		$em->remove($task);
	}
	$em->remove($resp);
	$em->flush();
	}
	public function deleteGroupAPIAction(Request $request)//API Method
	{
		$em =$this->getDoctrine()->getManager();
		$group= $em->getRepository('SMARTASKHomeBundle:Groupe')->find(intval( $request->get('group_id') ) );
		$listtask = $em->getRepository('SMARTASKHomeBundle:Task')->findBy(array('group' => $group));
		foreach ($listtask as $task) {
			$em->remove($task);
		}
		$em->remove($group);
		$em->flush();
	}
	public function deleteTaskAPIAction(Request $request) // API Method
	{
		$em =$this->getDoctrine()->getManager();
		$task= $em->getRepository('SMARTASKHomeBundle:Task')->find(intval( $request->get('task_id') ) );
		$em->remove($task);
		$em->flush();
	}
	public function postContactAction(Request $request)//API Method
	{
		$userManager = $this->container->get('fos_user.user_manager');
		$user = $userManager->findUserBy(array('id'=>$request->get('user_id')));
	
		$repository = $this->getDoctrine()->getManager()->getRepository('SMARTASKHomeBundle:Contact');
		$contact = $repository->find($request->get('id'));
		if (!$contact){
			$contact = new Contact();
		}
		$contact->setUser($user);// au cas ou on voudrait changer le contact de proprietaire
		$contact->setName($request->get('name'));
		$contact->setEmail($request->get('email'));
		$em =$this->getDoctrine()->getManager();
		$em->persist($contact);
		$em->flush();
	
		return $contact;
	}
	public function postTaskAction(Request $request)// API method
	{
		$userManager = $this->container->get('fos_user.user_manager');
		$user = $userManager->findUserBy(array('id'=>$request->get('user_id')));
		$repository = $this->getDoctrine()->getManager()->getRepository('SMARTASKHomeBundle:Task');
		$em =$this->getDoctrine()->getManager();
		$task = $repository->find($request->get('id'));
		if (!$task){//s'il n'existe pas on le cr�er et on l'ajoute a l'utilisateur
			$task = new Task();
			$user->getTasks()->add($task);
		}
		$task->setTitre($request->get('titre'));
		$groupe = $em->getRepository('SMARTASKHomeBundle:Groupe')->find(intval( $request->get('group_id') ) );
		$resp   = $em->getRepository('SMARTASKUserBundle:User')->find(intval( $request->get('resp_id') ) );
		$manager   = $em->getRepository('SMARTASKUserBundle:User')->find(intval( $request->get('manager_id') ) );
		$date = new \DateTime($request->get('date'));
		$time = new \DateTime($request->get('time'));
		$task->setLocalisation($request->get('localisation'));
		$task->setGroup($groupe);
		$task->setResp($resp);
		$task->setManager($manager);
		$task->setDate($date);
		$task->setTime($time);
		$task->setdescription($request->get('description'));
		$task->setIsalarmeon($request->get('isalarmeon'));
		$dest_resp = $userManager->findUserBy(array('email'=>$task->getResp()->getEmail()));
		$dest_resp->getGroupes()->add($task->getGroup());// ajout du groupe au responsable
		$dest_resp->getTasks()->add($task);// ajout de l'activit� au responsable
		$em->persist($task);
		$em->flush();
		return $task;
	}
	
	public function postGroupAction(Request $request)//API Method
	{
		$userManager = $this->container->get('fos_user.user_manager');
		$user = $userManager->findUserBy(array('id'=>$request->get('user_id')));
		$repository = $this->getDoctrine()->getManager()->getRepository('SMARTASKHomeBundle:Groupe');
		$group = $repository->find($request->get('id'));
		if (!$group){//s'il n'existe pas on le cr�er et on l'ajoute a l'utilisateur
			$group = new Groupe();
			$user->getGroupes()->add($group);
		}
		$group->setNom($request->get('nom'));
		$group->setdescription($request->get('description'));
		$em =$this->getDoctrine()->getManager();
		$em->persist($group);
		$em->flush();
		return $group;
	}
	public function getUserAction(Request $request) //API Method
	{
	
		$logger = $this->container->get('logger');
		$logger->info('getUserAction: '.$request->get('email'));
		$userManager = $this->container->get('fos_user.user_manager');
		$user = $userManager->findUserBy(array('email'=>$request->get('email')));
		$formatted = [];
		$formatted[] = [
				'id' => $user->getId(),
				'username' => $user->getUsername(),
				'email' => $user->getEmail(),
				'password'=>$user->getPassword(),
		];
		return new JsonResponse($formatted);
	}
	
	public function getAllContactsAction(Request $request) //API Method
	{
		$userManager = $this->container->get('fos_user.user_manager');
		$user = $userManager->findUserBy(array('id'=>$request->get('user_id')));
		//$listContacts = $user->getContacts();// on renvoit les taches associ�es a l'utilisateur
		$userrep = $this->getDoctrine()->getManager()->getRepository('SMARTASKUserBundle:User');
		$listContacts = $userrep->getRegisteredContactBuilder($user->getId())->getQuery()->getResult();
		$formatted = [];
		foreach ($listContacts as $contact) {
			$formatted[] = [
					'id' => $contact->getId(),
					'name' => $contact->getUsername(),
					'email' => $contact->getEmail(),
			];
		}
	
		return new JsonResponse($formatted);
	}
	
	public function getAllTasksAction(Request $request)//API Method
	{
		$userManager = $this->container->get('fos_user.user_manager');
		$user = $userManager->findUserBy(array('id'=>$request->get('user_id')));
		$listTasks = $user->getTasks();
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
	
	public function getAllGroupsAction(Request $request)//API Method
	{
	
		$userManager = $this->container->get('fos_user.user_manager');
		$user = $userManager->findUserBy(array('id'=>$request->get('user_id')));
		$listGroups = $user->getGroupes();
		$formatted = [];
		foreach ($listGroups as $group) {
			$formatted[] = [
					'id'   =>$group->getId(),
					'name' => $group->getNom(),
					'description' => $group->getDescription(),
					'description' => $group->getDescription(),
			];
		}
	
		return new JsonResponse($formatted);
	}
}
