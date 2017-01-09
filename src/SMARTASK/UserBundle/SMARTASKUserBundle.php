<?php

namespace SMARTASK\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SMARTASKUserBundle extends Bundle
{
	public function getParent()
	{
		return 'FOSUserBundle';
	}
}
