<?php

namespace SMARTASK\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;

class DefaultController extends Controller
{
	
	
	public function accueilAction(Request $request){
	
		// envoie de mail pour les fans
		$logger = $this->container->get('logger');
		$logger->info('sendmailAction');
	
		// je vérifie si elle est de type POST
		if($request->isMethod('POST'))
		{
			$logger->info('sendmailAction POST');
			$name = $request->get('name');
			$comments = $request->get('comments');
			$mail = $request->get('email');
	
			$logger->info('sendmailAction $$name : '.$name);
			$logger->info('sendmailAction $$mail  : '.$mail);
	
			$message = \Swift_Message::newInstance()
			->setContentType('text/html')
			->setSubject("Messag from Fan of SmarTask")
			->setFrom($mail)
			->setTo("smartask.project@gmail.com")
			->setBody($comments);
				
			$this->get('mailer')->send($message);
	
		}
		// gere la connection
		$session = $request->getSession();
	
		$authErrorKey = Security::AUTHENTICATION_ERROR;
		$lastUsernameKey = Security::LAST_USERNAME;
	
		// get the error if any (works with forward and redirect -- see below)
		if ($request->attributes->has($authErrorKey)) {
			$error = $request->attributes->get($authErrorKey);
		} elseif (null !== $session && $session->has($authErrorKey)) {
			$error = $session->get($authErrorKey);
			$session->remove($authErrorKey);
		} else {
			$error = null;
		}
	
		if (!$error instanceof AuthenticationException) {
			$error = null; // The value does not come from the security component.
		}
	
		// last username entered by the user
		$lastUsername = (null === $session) ? '' : $session->get($lastUsernameKey);
	
		$csrfToken = $this->has('security.csrf.token_manager')
		? $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue()
		: null;
		return $this->render('SMARTASKUserBundle:Default:navbar.html.twig',array('last_username' => $lastUsername,
				'error' => $error,'csrf_token' => $csrfToken));
	
	}
  
}
