<?php

namespace SMARTASK\HomeBundle\EventListner;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SMARTASK\HomeBundle\SMARTASKHomeEvents;


class APICalledListner implements EventSubscriberInterface
{
	private $mailer;

	public function __construct(\Swift_Mailer $mailer)
	{
		$this->mailer = $mailer;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function getSubscribedEvents()
	{
		return array(
				SMARTASKHomeEvents::API_CALLED => 'onApiCalled'
		);
	}

	public function onApiCalled(\SMARTASK\HomeBundle\Event\FormEvent $event)
	{
		//envoyer un mail

		//$event->setResponse(??);
	}
}