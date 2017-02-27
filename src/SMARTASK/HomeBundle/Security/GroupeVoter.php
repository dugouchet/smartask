<?php
namespace SMARTASK\HomeBundle\Security;

 
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use SMARTASK\HomeBundle\Entity\Groupe;
use SMARTASK\UserBundle\Entity\User;

class GroupeVoter extends Voter	 {
	
	// these strings are just invented: you can use anything
	const VIEW = 'view';
	const EDIT = 'edit';
	
	protected function supports($attribute, $subject)
	{
		// if the attribute isn't one we support, return false
		if (!in_array($attribute, array(self::VIEW, self::EDIT))) {
			return false;
		}
	
		// only vote on Groupe objects inside this voter
		if (!$subject instanceof Groupe) {
			return false;
		}
	
		return true;
	}
	
	protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
	{
		$user = $token->getUser();
	
		if (!$user instanceof User) {
			// the user must be logged in; if not, deny access
			return false;
		}
	
		// you know $subject is a Post object, thanks to supports
		/** @var Groupe $group */
		$group = $subject;
	
		switch ($attribute) {
			case self::VIEW:
				return $this->canView($group, $user); // to change !
			case self::EDIT:
				return $this->canEdit($group, $user);
		}
	
		throw new \LogicException('This code should not be reached!');
	}
	
	private function canView(Groupe $group, User $user)
	{
		// if they can edit, they can view
		if ($this->canEdit($group, $user)) {
			return false;
		}
	
		// the Groupe object could have, for example, a method isPrivate()
		// that checks a boolean $private property
		//return !$group->isPrivate();
		return false;
	}
	
	private function canEdit(Groupe $group, User $user)
	{
		// this assumes that the data object has a getOwner() method
		// to get the entity of the user who owns this data object
		
		foreach ($group->getUsers() as $usergroup ) {
			
			if ($usergroup === $user) {
				return true ;
			}
		}
		return false ;
	}
}