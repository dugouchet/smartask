<?php

namespace SMARTASK\HomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use SMARTASK\HomeBundle\Entity\Contact;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SMARTASK\HomeBundle\Entity\Groupe;
use SMARTASK\HomeBundle\Entity\Task;
use SMARTASK\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use SMARTASK\HomeBundle\Form\TaskType;
use SMARTASK\HomeBundle\Form\GroupeType;
use SMARTASK\HomeBundle\Form\ContactType;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DefaultController extends Controller {
	
	public function findtaskAction(Request $request) {
		$logger = $this->container->get ( 'logger' );
		$logger->info ( 'findtask' );
		// je v�rifie si elle est de type POST
		if ($request->isMethod('POST'))
		{
			$logger->info('findtask POST');
			$keyword = $request->get('keyword');
			$logger->info('findtask $keyword : '.$keyword);			
			$finder = $this->container->get('fos_elastica.finder.app.task');
			$results = $finder->find($keyword);
			
			foreach($results as $task)	{
			    $logger->info('findtask result'.$task->getTitre());
			}
		}

		$user = $this->getUser();// Pour r�cup�rer le service UserManager du bundle
		$listTasks = $user->getTasks();
		$nbtask = count($listTasks);
		return $this->render('SMARTASKHomeBundle:Default:activity.html.twig',array( 'listTasks' => $listTasks,'nbtask'=>$nbtask ));	
	}
	
	public function comments_groupAction($groupId){
		$em =$this->getDoctrine()->getManager();
		$group= $em->getRepository('SMARTASKHomeBundle:Groupe')->find( $groupId );
		return $this->render('SMARTASKHomeBundle:Default:commentsGroup.html.twig',array('group' => $group));
	}
	
	public function deleteTaskGroupAction($taskId, $groupId){
		$em =$this->getDoctrine()->getManager();
		$task = $em->getRepository('SMARTASKHomeBundle:Task')->find($taskId);
		$em->remove($task);
		$em->flush();
		$this->activity_groupAction($groupId);
	}
	
	public function activity_groupAction($id){
		$em =$this->getDoctrine()->getManager();
		$group= $em->getRepository('SMARTASKHomeBundle:Groupe')->find( $id );
		$listTasks = $em->getRepository('SMARTASKHomeBundle:Task')->findBy(array('group' => $group));
		$security = $this->get('security.authorization_checker');
		if (false === $security->isGranted('edit', $group)) {
			throw new AccessDeniedHttpException();
		}
		$nbtask = count($listTasks);
		return $this->render('SMARTASKHomeBundle:Default:listTaskGroup.html.twig',array('listTasks' => $listTasks,'group' => $group,'nbtask'=>$nbtask));
	}
	public function remove_person_from_groupAction($userId, $groupId){
		$em =$this->getDoctrine()->getManager();
		$group= $em->getRepository('SMARTASKHomeBundle:Groupe')->find( $groupId );
		$user=$em->getRepository('SMARTASKUserBundle:User')->findOneBy(array('id' => $userId));
		$user->removeGroupe($group);
		$em->flush();
		return $this->redirectToRoute('list_members_group',array('id' => $groupId));
	}
	public function listMembersGroupAction($id){
		$em =$this->getDoctrine()->getManager();
		$group= $em->getRepository('SMARTASKHomeBundle:Groupe')->find( $id );
		
		$security = $this->get('security.authorization_checker');
		if (false === $security->isGranted('edit', $group)) {
			throw new AccessDeniedHttpException();
		}
		
		return $this->render('SMARTASKHomeBundle:Default:listMembersGroup.html.twig',array('group' => $group));
	}
	
	public function addcontactgroupAction (Request $request, $id){
		$user = $this->getUser();// Pour r�cup�rer le service UserManager du bundle
		$em =$this->getDoctrine()->getManager();
		$group= $em->getRepository('SMARTASKHomeBundle:Groupe')->find( $id );
		
		$security = $this->get('security.authorization_checker');
		if (false === $security->isGranted('edit', $group)) {
			throw new AccessDeniedHttpException();
		}
		
		if ($request->isMethod('POST')){
			$emailcontact = $request->get('email');
			$logger = $this->container->get('logger');
			$logger->info('addcontactgroupAction email :'.$emailcontact);
			$addeduser = $em->getRepository('SMARTASKUserBundle:User')->findOneBy(array('email' => $emailcontact));
			if ($addeduser){
			    $addeduser->getGroupes()->add($group);
			    $logger->info('addcontactgroupAction user ajout�');
			    $em->persist($group);
			    $em->persist($addeduser);
			    $em->flush();
			    return $this->redirectToRoute('add_contact_group',array('id' => $id));
			}else{
				return $this->render('SMARTASKHomeBundle:Default:error.html.twig',array('msg' => "L'utilisateur n'est pas encore enregistre"));
			}
		}
		return $this->render('SMARTASKHomeBundle:Default:addperson.html.twig',array('group' => $group,'list_contact' => $user->getContacts()));
	}
	
	
	public function open_group_detailAction($id){ 
		$em =$this->getDoctrine()->getManager();
		$group= $em->getRepository('SMARTASKHomeBundle:Groupe')->find( $id );
		$security = $this->get('security.authorization_checker');
		if (false === $security->isGranted('edit', $group)) {
			throw new AccessDeniedHttpException();
		}
		return $this->render('SMARTASKHomeBundle:Default:detailgroup.html.twig',array('group' => $group));
	}
	/**
	 * @Route("/")
	 * @Template()
	 */
	public function accueilAction(Request $request){
		
		// envoie de mail pour les fans
		$logger = $this->container->get('logger');
		$logger->info('sendmailAction');
				
		// je v�rifie si elle est de type POST
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
	
		$csrfToken = $this->has('security.csrf.token_manager') ? $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue(): null;

		//add cache
		$response = $this->render('SMARTASKHomeBundle:Default:accueil.html.twig',array('last_username' => $lastUsername,'error' => $error,'csrf_token' => $csrfToken));
		$response->setPublic();	
		$response->setSharedMaxAge(3600);
		
		return  $response ;		
	}
	

	public function activityAction(Request $request)
	{
		$user = $this->getUser();// Pour r�cup�rer le service UserManager du bundle
		$listTasks = $user->getTasks();
		$nbtask = count($listTasks);
		return $this->render('SMARTASKHomeBundle:Default:activity.html.twig',array( 'listTasks' => $listTasks,'nbtask'=>$nbtask ));
	}
	
	
	public function taskAction(Request $request)
	{
		$logger = $this->container->get('logger');
		$user = $this->getUser();// Pour r�cup�rer le service UserManager du bundle
		$task = new Task();
		$task->setManager($user);
		$task->setIsalarmeon(1);
		$task->setDate(new \DateTime("now"));
		$task->setTime(new \DateTime("now"));
		$form   = $this->get('form.factory')->create(TaskType::class, $task);
	
		if ($request->isMethod('POST')){//s'il l'utilisateur veut enregistrer sa tache
			$form->handleRequest($request);
			if($form->isValid()){
				$em =$this->getDoctrine()->getManager();
				$user->getTasks()->add($task);// ajout dans l'activit� du decideur
				$userrep = $this->getDoctrine()->getManager()->getRepository('SMARTASKUserBundle:User');
				$dest_resp = $userrep->findOneBy(array('email'=>$task->getResp()->getEmail()));
				$logger->info('taskAction email :'.$task->getResp()->getEmail());
				$logger->info('taskAction email :'.$dest_resp->getEmail());
				if ($dest_resp){ // s'il l'uutilisateur est bien inscrit on lui envoit la tache sinon rien
					if($task->getGroup()){//s'il y a un groupe de defini
					    if ( !$userrep->isUserBelongToGroup($task->getGroup()->getId(),$dest_resp->getId()) ){
						    $logger->info('taskAction le user n\'appartient pas au group');
						    $dest_resp->getGroupes()->add($task->getGroup());// ajout du groupe au responsable s'il n'est pas deja dans le groupe
					    }
					}else{
				        return $this->render('SMARTASKHomeBundle:Default:error.html.twig',array('msg' => "Vous devez renseigner ou créer un groupe"));
						
					}
					$dest_resp->getTasks()->add($task);// ajout de l'activit� au responsable
					$em->persist($task);
					$em->flush();
					unset($task);
					unset($form);
				}else{
					// envoyer un msg d'erreur comme sweetalert
					return $this->render('SMARTASKHomeBundle:Default:error.html.twig',array('msg' => "L'utilisateur n'est pas encore enregistre"));
					//return new Response("The User is not registered yet ..");
				}
				$listTasks = $user->getTasks();
				$nbtask = count($listTasks);
				return $this->render('SMARTASKHomeBundle:Default:activity.html.twig',array('listTasks' => $user->getTasks(),'nbtask'=>$nbtask  ));
			}
		}
		$listTasks = $user->getTasks();
		return $this->render('SMARTASKHomeBundle:Default:task.html.twig',array('form' => $form->createView(),'listTasks' => $listTasks));
	}
	
	
    public function groupAction(Request $request)
    {
    	$user = $this->getUser();// Pour r�cup�rer le service UserManager du bundle
    	$group = new Groupe();
    	$form   = $this->get('form.factory')->create(GroupeType::class, $group);
    	 
    	if ($request->isMethod('POST')){
    		$form->handleRequest($request);
    		if($form->isValid()){
    			$em =$this->getDoctrine()->getManager();
    			$user->getGroupes()->add($group);
    			$em->persist($group);
    			$em->flush();
    			unset($group);
    			unset($form);
    			return $this->redirectToRoute('smartask_group_homepage');
    		}
    		}
    	
        return $this->render('SMARTASKHomeBundle:Default:group.html.twig',array('form' => $form->createView(), 'listGroups' => $user->getGroupes() ));
    }
    
    
    
    public function contactAction(Request $request)
    {
    	
    	$user = $this->getUser();// Pour r�cup�rer le service UserManager du bundle
    	$contact = new Contact();
    	$contact->setUser($user);
    	$form   = $this->get('form.factory')->create(ContactType::class, $contact);
    	
    	if ($request->isMethod('POST')){
    		$form->handleRequest($request);
    		if($form->isValid()){
    			$em =$this->getDoctrine()->getManager();
    			$em->persist($contact);
    			$em->flush();
    			unset($contact);
    			unset($form);
    			return $this->redirectToRoute('smartask_contact_homepage');
    		}
    	}
    	return $this->render('SMARTASKHomeBundle:Default:contact.html.twig',array('form' => $form->createView(), 'listContacts' => $user->getContacts() ) );
    }
    
    public function deleteContactAction($id){
    	$em =$this->getDoctrine()->getManager();
    	$repository_contact = $em->getRepository('SMARTASKHomeBundle:Contact');
    	$repository_task = $em->getRepository('SMARTASKHomeBundle:Task');
    	$contact = $repository_contact->find($id);
    	$listtask = $repository_task->findBy(//on supprime les taches associ�es � ce contact
    			array('resp' => $contact));
    	foreach ($listtask as $task) {
    		$em->remove($task);;
    	}
    	
    	$em->remove($contact);
    	$em->flush();
    	return $this->redirectToRoute('smartask_contact_homepage');
    }
    public function deleteGroupAction($id){
    	$em =$this->getDoctrine()->getManager();
    	$repository_group = $em->getRepository('SMARTASKHomeBundle:Groupe');
    	$repository_task = $em->getRepository('SMARTASKHomeBundle:Task');
    	$group = $repository_group->find($id);
    	$listtask = $repository_task->findBy(
    			array('group' => $group));
    	foreach ($listtask as $task) {
    		$em->remove($task);;
    	}
    	$em->remove($group);
    	$em->flush();
    	return $this->redirectToRoute('smartask_group_homepage');
    }
    public function deleteTaskAction($id){
    	$em =$this->getDoctrine()->getManager();
    	$repository_task = $em->getRepository('SMARTASKHomeBundle:Task');
    	$task = $repository_task->find($id);
    	$em->remove($task); 
    	$em->flush();
    	return $this->redirectToRoute('smartask_activity_homepage');
    }
  
}
