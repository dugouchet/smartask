<?php

namespace SMARTASK\CommentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('SMARTASKHomeBundle:Default:index.html.twig');
    }
}
