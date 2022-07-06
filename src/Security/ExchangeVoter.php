<?php
namespace App\Security;

use App\Entity\Exchange;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ExchangeVoter extends Voter
{
    // these strings are just invented: you can use anything
    const VIEW = 'view';
    const EDIT = 'edit';
    CONST DELETE = 'delete';

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])) {
            return false;
        }

        // only vote on `Exchange` objects
        if (!$subject instanceof Exchange) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a Exchange object, thanks to `supports()`
        /** @var Exchange $Exchange */
        $Exchange = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($Exchange, $user);
            case self::EDIT:
                return $this->canEdit($Exchange, $user);
            case self::DELETE:
                return $this->canDelete($Exchange, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Exchange $Exchange, User $user): bool
    {
        // if they can edit, they can view
        if ($this->canEdit($Exchange, $user)) {
            return true;
        }
        // the Exchange object could have, for example, a method `isPrivate()`
        return ($Exchange->getOwner() == $user || $Exchange->getProposer() == $user || $this->security->isGranted(Role::ADMIN));
    }

    private function canEdit(Exchange $Exchange, User $user): bool
    {
        // this assumes that the Exchange object has a `getOwner()` method
        return ($Exchange->getOwner() == $user || $Exchange->getProposer() == $user || $this->security->isGranted(Role::ADMIN));
    }

    private function canDelete(Exchange $Exchange, User $user): bool
    {
        // this assumes that the Exchange object has a `getOwner()` method
        return ($Exchange->getOwner() == $user || $Exchange->getProposer() == $user || $this->security->isGranted(Role::ADMIN));
    }
}
