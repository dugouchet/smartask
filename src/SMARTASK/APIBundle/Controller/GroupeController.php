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

class GroupeController extends Controller
{
	/**
	 * @Rest\View(serializerGroups={"group"})
	 * @Rest\Get("/api/users/{user_id}/groups")
	 */
	public function getGroupsAction(Request $request)
	{
		$userManager = $this->container->get('fos_user.user_manager');
		$user = $userManager->findUserBy(array('id'=>$request->get('user_id')));
	
		if (empty($user)) {
			return $this->userNotFound();
		}
	
		return $user->getGroupes();
	}

	/**
	 * @Rest\View(serializerGroups={"group"})
	 * @Rest\Get("/api/users/{user_id}/groups/{group_id}")
	 */
	public function getGroupAction(Request $request)
	{
		// ..
	}
	/**
	 * @Rest\View(statusCode=Response::HTTP_CREATED)
	 * @Rest\Post("/api/postgroup")
	 */
	public function postGroupAction(Request $request)//API Method
	{
		$group = new Groupe();
		$form = $this->createForm(GroupeType::class, $group);
		$form->submit($request->request->all()); // Validation des données
	
		if ($form->isValid()) {
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
		else
		{
			return $form;
		}
	}
	/**
	 * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
	 * @Rest\Delete("/api/deletegroup/{group_id}")
	 */
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
	
	private function userNotFound()
	{
		return \FOS\RestBundle\View\View::create(['message' => 'user not found'], Response::HTTP_NOT_FOUND);
	}
}