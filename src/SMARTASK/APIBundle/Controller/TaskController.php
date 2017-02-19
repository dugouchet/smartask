<?php
namespace SMARTASK\APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use SMARTASK\HomeBundle\Entity\Contact;
use SMARTASK\HomeBundle\Entity\Task;
use SMARTASK\HomeBundle\Entity\Groupe;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as Rest; // alias pour toutes les annotations
use FOS\RestBundle\View\ViewHandler;
use FOS\RestBundle\View\View; // Utilisation de la vue de FOSRestBundle
use Symfony\Component\HttpFoundation\Response;
use SMARTASK\HomeBundle\Form\GroupeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Time;
use SMARTASK\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use SMARTASK\HomeBundle\Form\TaskType;
use SMARTASK\HomeBundle\Form\ContactType;

class TaskController extends Controller
{
	/**
	 * @Rest\View(serializerGroups={"task"})
	 * @Rest\Get("/api/users/{user_id}/tasks")
	 */
	public function getTasksAction(Request $request)
	{
		$userManager = $this->container->get('fos_user.user_manager');
		$user = $userManager->findUserBy(array('id'=>$request->get('user_id')));
	
		if (empty($user)) {
			return $this->userNotFound();
		}
	
		return $user->getTasks();
	}
	
	/**
	 * @Rest\View(serializerGroups={"task"})
	 * @Rest\Get("/api/users/{user_id}/tasks/{tasks_id}")
	 */
	public function getTaskAction(Request $request)
	{
		// ..
	}
	
	/**
	 * @Rest\View(statusCode=Response::HTTP_CREATED)
	 * @Rest\Post("/api/posttask")
	 */
	public function postTaskAction(Request $request)// API method
	{
		$task = new Task();
		$form = $this->createForm(TaskType::class, $task);
	
		$form->submit($request->request->all()); // Validation des données
	
		if ($form->isValid()) {
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
				
		} else {
			return $form;
		}
	}

		/**
	 * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
	 * @Rest\Delete("/api/deletetask/{task_id}")
	 */
	public function deleteTaskAPIAction(Request $request) // API Method
	{
		$em =$this->getDoctrine()->getManager();
		$task= $em->getRepository('SMARTASKHomeBundle:Task')->find(intval( $request->get('task_id') ) );
		$em->remove($task);
		$em->flush();
	}
}