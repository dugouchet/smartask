<?php

namespace SMARTASK\HomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use SMARTASK\HomeBundle\Entity\Contact;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SMARTASK\HomeBundle\Entity\Groupe;
use SMARTASK\HomeBundle\Entity\Task;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Time;
use Symfony\Component\HttpFoundation\JsonResponse;
use SMARTASK\UserBundle\Entity\User ;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use SMARTASK\HomeBundle\Form\TaskType;
use SMARTASK\HomeBundle\Form\GroupeType;
use SMARTASK\HomeBundle\Form\ContactType;

class DefaultController extends Controller
{
	
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
		return $this->render('SMARTASKHomeBundle:Default:listTaskGroup.html.twig',array('listTasks' => $listTasks,'group' => $group));
		
	}
	public function remove_person_from_group($userId, $groupId){
		$em =$this->getDoctrine()->getManager();
		$group= $em->getRepository('SMARTASKHomeBundle:Groupe')->find( $groupId );
		$user=$em->getRepository('SMARTASKuserBundle:User')->find( $userId );
		$group->getUsers()->remove($user);
		return $this->render('SMARTASKHomeBundle:Default:listMembersGroup.html.twig',array('group' => $group));
	}
	public function listMembersGroupAction($id){
		$em =$this->getDoctrine()->getManager();
		$group= $em->getRepository('SMARTASKHomeBundle:Groupe')->find( $id );
		return $this->render('SMARTASKHomeBundle:Default:listMembersGroup.html.twig',array('group' => $group));
	}
	
	public function addcontactgroupAction (Request $request, $id){
		$user = $this->getUser();// Pour r�cup�rer le service UserManager du bundle
		$em =$this->getDoctrine()->getManager();
		$group= $em->getRepository('SMARTASKHomeBundle:Groupe')->find( $id );
		
		if ($request->isMethod('POST')){
			$emailcontact = $request->get('email');
			$logger = $this->container->get('logger');
			$logger->info('addcontactgroupAction email :'.$emailcontact);
			$addeduser = $em->getRepository('SMARTASKUserBundle:User')->findBy(array('email' => $emailcontact));
			if ($addeduser){
			    $group->getUsers()->add($addeduser);
			    $logger->info('addcontactgroupAction user ajout�');
			    return $this->render('SMARTASKHomeBundle:Default:detailgroup.html.twig',array('group' => $group));
			}else{
				return $this->render('SMARTASKHomeBundle:Default:error.html.twig',array('msg' => "L'utilisateur n'est pas encore enregistre"));
			}
		}
		return $this->render('SMARTASKHomeBundle:Default:addperson.html.twig',array('group' => $group,'list_contact' => $user->getContacts()));
	}
	
	
	public function open_group_detailAction($id){ 
		$em =$this->getDoctrine()->getManager();
		$group= $em->getRepository('SMARTASKHomeBundle:Groupe')->find( $id );
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
	
		$csrfToken = $this->has('security.csrf.token_manager')
		? $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue()
		: null;
		return $this->render('SMARTASKHomeBundle:Default:accueil.html.twig',array('last_username' => $lastUsername,
				'error' => $error,'csrf_token' => $csrfToken));
	
	}
	
	public function deleteContactAPIAction(Request $request)//API Method
	{//pas besoin d'utilisateur car la tache a un id unique
		$em =$this->getDoctrine()->getManager();
		$resp= $em->getRepository('SMARTASKHomeBundle:Contact')->find(intval( $request->get('resp_id') ) );
		$listtask = $em->getRepository('SMARTASKHomeBundle:Task')->findBy(array('resp' => $resp));
		foreach ($listtask as $task) {
			$em->remove($task);
		}
		$em->remove($resp);
		$em->flush();
	}
	public function deleteGroupAPIAction(Request $request)//API Method
	{
		$em =$this->getDoctrine()->getManager();
		$group= $em->getRepository('SMARTASKHomeBundle:Groupe')->find(intval( $request->get('group_id') ) );
		$listtask = $em->getRepository('SMARTASKHomeBundle:Task')->findBy(array('group' => $group));
		foreach ($listtask as $task) {
			$em->remove($task);
		}
		$em->remove($group);
		$em->flush();
	}
	public function deleteTaskAPIAction(Request $request) // API Method
	{
		$em =$this->getDoctrine()->getManager();
		$task= $em->getRepository('SMARTASKHomeBundle:Task')->find(intval( $request->get('task_id') ) );
		$em->remove($task);
		$em->flush();
	}
	public function postContactAction(Request $request)//API Method
	{
		$userManager = $this->container->get('fos_user.user_manager');
		$user = $userManager->findUserBy(array('id'=>$request->get('user_id')));
		
		$repository = $this->getDoctrine()->getManager()->getRepository('SMARTASKHomeBundle:Contact');
		$contact = $repository->find($request->get('id'));
		if (!$contact){
			$contact = new Contact();	
		}
		$contact->setUser($user);// au cas ou on voudrait changer le contact de proprietaire
		$contact->setName($request->get('name'));
		$contact->setEmail($request->get('email'));
		$em =$this->getDoctrine()->getManager();
		$em->persist($contact);
		$em->flush();
	
		return $contact;
	}
	public function postTaskAction(Request $request)// API method
	{   
		$userManager = $this->container->get('fos_user.user_manager');
		$user = $userManager->findUserBy(array('id'=>$request->get('user_id')));
		$repository = $this->getDoctrine()->getManager()->getRepository('SMARTASKHomeBundle:Task');
		$em =$this->getDoctrine()->getManager();
		$task = $repository->find($request->get('id'));
		if (!$task){//s'il n'existe pas on le cr�er et on l'ajoute a l'utilisateur
			$task = new Task();
			$user->getTasks()->add($task);
		}
		$task->setTitre($request->get('titre'));
		$groupe = $em->getRepository('SMARTASKHomeBundle:Groupe')->find(intval( $request->get('group_id') ) );
		$resp   = $em->getRepository('SMARTASKUserBundle:User')->find(intval( $request->get('resp_id') ) );
		$manager   = $em->getRepository('SMARTASKUserBundle:User')->find(intval( $request->get('manager_id') ) );
		$date = new \DateTime($request->get('date'));
		$time = new \DateTime($request->get('time'));
		$task->setLocalisation($request->get('localisation'));
		$task->setGroup($groupe);
		$task->setResp($resp);
		$task->setManager($manager);
		$task->setDate($date);
		$task->setTime($time);
		$task->setdescription($request->get('description'));
		$task->setIsalarmeon($request->get('isalarmeon'));
		$dest_resp = $userManager->findUserBy(array('email'=>$task->getResp()->getEmail()));
		$dest_resp->getGroupes()->add($task->getGroup());// ajout du groupe au responsable
		$dest_resp->getTasks()->add($task);// ajout de l'activit� au responsable
		$em->persist($task);
		$em->flush();
	
		return $task;
	}
	
	public function postGroupAction(Request $request)//API Method
	{
		$userManager = $this->container->get('fos_user.user_manager');
		$user = $userManager->findUserBy(array('id'=>$request->get('user_id')));
		$repository = $this->getDoctrine()->getManager()->getRepository('SMARTASKHomeBundle:Groupe');
		$group = $repository->find($request->get('id'));
		if (!$group){//s'il n'existe pas on le cr�er et on l'ajoute a l'utilisateur
			$group = new Groupe();
			$user->getGroupes()->add($group);
		}
		$group->setNom($request->get('nom'));
		$group->setdescription($request->get('description'));
		$em =$this->getDoctrine()->getManager();
		$em->persist($group);
		$em->flush();
		
		return $group;
	}
	public function getUserAction(Request $request) //API Method
	{
		
		$logger = $this->container->get('logger');
		$logger->info('getUserAction: '.$request->get('email'));
		$userManager = $this->container->get('fos_user.user_manager');
		$user = $userManager->findUserBy(array('email'=>$request->get('email')));
		$formatted = [];
		$formatted[] = [
				'id' => $user->getId(),
				'username' => $user->getUsername(),
				'email' => $user->getEmail(),
				'password'=>$user->getPassword(),
		];
		return new JsonResponse($formatted);
	}
	
	public function getAllContactsAction(Request $request) //API Method
	{
		$userManager = $this->container->get('fos_user.user_manager');
		$user = $userManager->findUserBy(array('id'=>$request->get('user_id')));
		//$listContacts = $user->getContacts();// on renvoit les taches associ�es a l'utilisateur
		$userrep = $this->getDoctrine()->getManager()->getRepository('SMARTASKUserBundle:User');
		$listContacts = $userrep->getRegisteredContactBuilder($user->getId())->getQuery()->getResult();
		$formatted = [];
		foreach ($listContacts as $contact) {
			$formatted[] = [
					'id' => $contact->getId(),
					'name' => $contact->getUsername(),
					'email' => $contact->getEmail(),
			];
		}
	
		return new JsonResponse($formatted);
	}
	
	public function getAllTasksAction(Request $request)//API Method
	{
	
		$userManager = $this->container->get('fos_user.user_manager');
		$user = $userManager->findUserBy(array('id'=>$request->get('user_id')));
		$listTasks = $user->getTasks();
		$formatted = [];
		foreach ($listTasks as $task) {
			$formatted[] = [
					'id'  => $task->getId(),
					'name'  => $task->getTitre(),
					'description' => $task->getDescription(),
					'localisation' => $task->getLocalisation(),
					'groupe_id' => $task->getGroup()->getId(),
					'resp_id' => $task->getResp()->getId(),
					'date' => $task->getDate(),
					'time' => $task->getTime(),
					'description' => $task->getDescription(),
					'isalarmeon' => $task->getIsalarmeon(),
			];
		}
	
		return new JsonResponse($formatted);
	}
	
	public function getAllGroupsAction(Request $request)//API Method
	{
	
		$userManager = $this->container->get('fos_user.user_manager');
		$user = $userManager->findUserBy(array('id'=>$request->get('user_id')));
		$listGroups = $user->getGroupes();
		$formatted = [];
		foreach ($listGroups as $group) {
			$formatted[] = [
					'id'   =>$group->getId(),
					'name' => $group->getNom(),
					'description' => $group->getDescription(),
					'description' => $group->getDescription(),
			];
		}
	
		return new JsonResponse($formatted);
	}
	public function activityAction(Request $request)
	{
		$user = $this->getUser();// Pour r�cup�rer le service UserManager du bundle
		$listTasks = $user->getTasks();
		return $this->render('SMARTASKHomeBundle:Default:activity.html.twig',array( 'listTasks' => $listTasks ));
	}
	
	
	public function taskAction(Request $request)
	{
		$logger = $this->container->get('logger');
		$user = $this->getUser();// Pour r�cup�rer le service UserManager du bundle
		$task = new Task();
		$task->setManager($user);
		$task->setIsalarmeon(1);
		$form   = $this->get('form.factory')->create(TaskType::class, $task);
	
		if ($request->isMethod('POST')){//s'il l'utilisateur veut enregistrer sa tache
			$form->handleRequest($request);
			if($form->isValid()){
				$em =$this->getDoctrine()->getManager();
				$user->getTasks()->add($task);// ajout dans l'activit� du decideur
				$userManager = $this->container->get('fos_user.user_manager');
				$userrep = $this->getDoctrine()->getManager()->getRepository('SMARTASKUserBundle:User');
				$dest_resp = $userManager->findUserBy(array('email'=>$task->getResp()->getEmail()));
				if ($dest_resp){ // s'il l'uutilisateur est bien inscrit on lui envoit la tache sinon rien
					if ( !$userrep->isUserBelongToGroup($task->getGroup()->getId(),$dest_resp->getId()) ){
						
						$logger->info('taskAction le user n\'appartient pas au group');
						$dest_resp->getGroupes()->add($task->getGroup());// ajout du groupe au responsable s'il n'est pas deja dans le groupe
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
				return $this->render('SMARTASKHomeBundle:Default:activity.html.twig',array('listTasks' => $user->getTasks() ));
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
