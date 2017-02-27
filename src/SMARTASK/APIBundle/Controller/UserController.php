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
use SMARTASK\UserBundle\Form\UserType;
use SMARTASK\HomeBundle\Event\FormEvent;
use SMARTASK\HomeBundle\SMARTASKHomeEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class UserController extends Controller
{
	/**
	 * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"user"})
	 * @Rest\Post("/api/users")
	 */
	public function postUsersAction(Request $request)
	{
		
		$user = new User();
		$form = $this->createForm(UserType::class, $user, ['validation_groups'=>['Default', 'New']]);
	
		$form->submit($request->request->all());
	
		if ($form->isValid()) {
			
			$dispatcher = $this->get('event_dispatcher');
			$event = new FormEvent($form, $request);
			$dispatcher->dispatch(SMARTASKHomeEvents::API_CALLED, $event);
			
			$encoder = $this->get('security.password_encoder');
			// le mot de passe en claire est encodé avant la sauvegarde
			$encoded = $encoder->encodePassword($user, $user->getPlainPassword());
			$user->setPassword($encoded);
	
			$em = $this->get('doctrine.orm.entity_manager');
			$em->persist($user);
			$em->flush();
			return $user;
		} else {
			return $form;
		}
	}
	
	/**
	 * @Rest\View(serializerGroups={"user"})
	 * @Rest\Put("/api/users/{id}")
	 */
	public function updateUserAction(Request $request)
	{
		return $this->updateUser($request, true);
	}
	
	/**
	 * @Rest\View(serializerGroups={"user"})
	 * @Rest\Patch("/api/users/{id}")
	 */
	public function patchUserAction(Request $request)
	{
		return $this->updateUser($request, false);
	}
	
	private function updateUser(Request $request, $clearMissing)
	{
		$user = $this->get('doctrine.orm.entity_manager')
		->getRepository('SMARTASKUserBundle:User')
		->find($request->get('id')); // L'identifiant en tant que paramètre n'est plus nécessaire
		/* @var $user User */
	
		if (empty($user)) {
			return $this->userNotFound();
		}
	
		if ($clearMissing) { // Si une mise à jour complète, le mot de passe doit être validé
			$options = ['validation_groups'=>['Default', 'FullUpdate']];
		} else {
			$options = []; // Le groupe de validation par défaut de Symfony est Default
		}
	
		$form = $this->createForm(UserType::class, $user, $options);
	
		$form->submit($request->request->all(), $clearMissing);
	
		if ($form->isValid()) {
			// Si l'utilisateur veut changer son mot de passe
			if (!empty($user->getPlainPassword())) {
				$encoder = $this->get('security.password_encoder');
				$encoded = $encoder->encodePassword($user, $user->getPlainPassword());
				$user->setPassword($encoded);
			}
			$em = $this->get('doctrine.orm.entity_manager');
			$em->merge($user);
			$em->flush();
			return $user;
		} else {
			return $form;
		}
	}
	
	/**
	 * @Rest\View(serializerGroups={"user"})
	 * @Rest\Get("/api/users")
	 */
	public function getUsersAction(Request $request)
	{
		$listUsers = $this->getDoctrine()->getManager()
		->getRepository('SMARTASKUserBundle:User')
		->findAll();
		
		return $listUsers ;
	}

	/**
	 * @Rest\View(serializerGroups={"user"})
	 * @Rest\Get("/api/users/{user_id}")
	 */
	public function getUserAction(Request $request)
	{
		$userManager = $this->container->get('fos_user.user_manager');
		$user = $userManager->findUserBy(array('id'=>$request->get('user_id')));
		
		if (empty($user)) {
			return $this->userNotFound();
		}
		
		return $user ;
	}
	
	private function userNotFound()
	{
		return \FOS\RestBundle\View\View::create(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
	}	
	
}