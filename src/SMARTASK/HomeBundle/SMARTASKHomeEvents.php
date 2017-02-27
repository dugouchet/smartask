<?php
namespace SMARTASK\HomeBundle;

final class SMARTASKHomeEvents{
		
	/**
	 * The CHANGE_PASSWORD_SUCCESS event occurs when the change password form is submitted successfully.
	 *
	 * This event allows you to set the response instead of using the default one.
	 *
	 * @Event("SMARTASK\HomeBundle\Event\FormEvent")
	 */
	const API_CALLED = 'api_called';
}