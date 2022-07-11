<?php
namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends Voter
{
    private $security = null;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    
    // these strings are just invented: you can use anything
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';
    const POSTADMIN = 'postAdmin';

    protected function supports($attribute, $subject): bool
    {
        $supportsAttribute = in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::POSTADMIN]);
        $supportsSubject = $subject instanceof User;

        return $supportsAttribute && $supportsSubject;
    }

    /**
     * @param string $attribute
     * @param User $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, $userObject, TokenInterface $token): bool
    {

        $currentUser = $token->getUser();

        if (!$userObject instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $user is a User object, thanks to `supports()`
        /** @var User $User */
        $UserObject = $userObject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($UserObject, $currentUser);
            case self::EDIT:
                return $this->canEdit($UserObject, $currentUser);
            case self::DELETE:
                return $this->canDelete($UserObject, $currentUser);
            // case self::POSTADMIN:
            //     return $this->canPostAdmin($UserObject, $currentUser);
            // case self::CREATE:
            //     return $this->canCreate($UserObject, $currentUser);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(User $userObject, User $currentUser): bool
    {
        // if they can edit, they can view
        if ($this->canEdit($userObject, $currentUser)) {
            return true;
        }
        // the User object could have, for example, a method `isPrivate()`
        return ($currentUser->getUser() == $userObject || $this->security->isGranted('ROLE_ADMIN'));
    }

    private function canEdit(User $userObject, User $currentUser): bool
    {
        // this assumes that the User object has a `getOwner()` method
        return ($currentUser->getUser() == $userObject || $this->security->isGranted('ROLE_ADMIN'));
    }

    private function canDelete(User $userObject, User $currentUser): bool
    {
        // this assumes that the User object has a `getOwner()` method
        return ($currentUser->getUser() == $userObject || $this->security->isGranted('ROLE_ADMIN'));
    }

    // public function canPostAdmin(User $userObject, User $currentUser): bool
    // {
    //     // this assumes that the User object has a `getOwner()` method
    //     return ($this->security->isGranted('ROLE_ADMIN'));
    // }
}
