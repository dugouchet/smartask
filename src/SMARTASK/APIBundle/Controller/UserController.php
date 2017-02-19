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

class UserController extends Controller
{
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
			return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
		}
		
		return $user ;
	}
}