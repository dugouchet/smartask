<?php
namespace SMARTASK\UserBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\UserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Redirection aprés enregistrement d'un utilisateur
 */
class RegistrationConfirmListener implements EventSubscriberInterface
{
	private $router;

	public function __construct(UrlGeneratorInterface $router)
	{
		$this->router = $router;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function getSubscribedEvents()
	{
		return array(
				FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistrationConfirm'
		);
	}

	public function onRegistrationConfirm(\FOS\UserBundle\Event\FormEvent $event)
	{
		$url = $this->router->generate('smartask_activity_homepage');

		$event->setResponse(new RedirectResponse($url));
	}
}