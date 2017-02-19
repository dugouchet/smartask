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

class ContactController extends Controller
{
	/**
	 * @Rest\View(serializerGroups={"contact"})
	 * @Rest\Get("/api/users/{user_id}/contacts")
	 */
	public function getContactsAction(Request $request)
	{
		$userManager = $this->container->get('fos_user.user_manager');
		$user = $userManager->findUserBy(array('id'=>$request->get('user_id')));
		
		if (empty($user)) {
			return $this->userNotFound();
		}
		
		return $user->getContacts();
	}

	/**
	 * @Rest\View(serializerGroups={"contact"})
	 * @Rest\Get("/api/users/{user_id}/contacts/{resp_id}")
	 */
	public function getContactAction(Request $request)
	{
		$contact = $this->getDoctrine()->getManager()
		->getRepository('SMARTASKHomeBundle:Contact')
		->find($request->get('resp_id'));
		
		if (empty($contact)) {
			return new JsonResponse(['message' => 'Contact not found'], Response::HTTP_NOT_FOUND);
		}
		
		return $contact ;
	}
	/**
	 * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"contact"})
	 * @Rest\Post("/api/postcontact")
	 */
	public function postContactAction(Request $request)//API Method
	{
		$userManager = $this->container->get('fos_user.user_manager');
		$user = $userManager->findUserBy(array('id'=>$request->get('user_id')));
		
		if (empty($user)) {
			return $this->userNotFound();
		}
		
		$contact = new Contact();
		$contact->setContact($contact);
		$form = $this->createForm(ContactType::class, $contact);
		
		// Le paramétre false dit à Symfony de garder les valeurs dans notre
		// entité si l'utilisateur n'en fournit pas une dans sa requête
		$form->submit($request->request->all());
		
		if ($form->isValid()) {
			$em = $this->get('doctrine.orm.entity_manager');
			$em->persist($contact);
			$em->flush();
			return $contact;
		} else {
			return $form;
		}
	
	}
	
	/**
	 * @Rest\View(statusCode=Response::HTTP_NO_CONTENT, serializerGroups={"contact"})
	 * @Rest\Delete("/api/deletecontact/{id}")
	 */
	public function deleteContactAction(Request $request)//API Method
	{//pas besoin d'utilisateur car la tache a un id unique
	$em =$this->getDoctrine()->getManager();
	$resp= $em->getRepository('SMARTASKHomeBundle:Contact')->find(intval( $request->get('id') ) );
	$listtask = $em->getRepository('SMARTASKHomeBundle:Task')->findBy(array('resp' => $resp));
	
	foreach ($listtask as $task) {
		$em->remove($task);
	}
	$em->remove($resp);
	$em->flush();
	}
	
	private function userNotFound()
	{
		return \FOS\RestBundle\View\View::create(['message' => 'user not found'], Response::HTTP_NOT_FOUND);
	}
}