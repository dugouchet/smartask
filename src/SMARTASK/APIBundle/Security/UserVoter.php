<?php
namespace SMARTASK\HomeBundle\Security;

 
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use SMARTASK\UserBundle\Entity\User;

class UserVoter extends Voter	 {
	
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
		if (!$subject instanceof User) {
			return false;
		}
		return true;
	}
	
	protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
	{
		$tokenUser = $token->getUser();
		var_dump($tokenUser);
		echo $tokenUser;
		\Doctrine\Common\Util\Debug::dump($tokenUser);
	
		if (!$tokenUser instanceof User) {
			// the user must be logged in; if not, deny access
			return false;
		}
	
		// you know $subject is a Post object, thanks to supports
		/** @var User $user */
		$user = $subject;
	
		switch ($attribute) {
			case self::VIEW:
				return $this->canView($user, $tokenUser); 
			case self::EDIT:
				return $this->canEdit($user, $tokenUser);
		}
	
		throw new \LogicException('This code should not be reached!');
	}
	
	private function canView(User $user, User $tokenUser)
	{
		// if they can edit, they can view
		if ($this->canEdit($group, $tokenUser)) {
			return false;
		}
	
		// the Groupe object could have, for example, a method isPrivate()
		// that checks a boolean $private property
		//return !$group->isPrivate();
		return false;
	}
	
	private function canEdit(User $user, User $tokenUser)
	{
		// this assumes that the data object has a getOwner() method
		// to get the entity of the user who owns this data object
		
			
			if ($user === $tokenUser) {
				return true ;
			}else {
				return false ;
			}		
	}
}