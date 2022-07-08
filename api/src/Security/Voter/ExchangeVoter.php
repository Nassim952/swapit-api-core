<?php
namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Exchange;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ExchangeVoter extends Voter
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

    protected function supports(string $attribute, $subject): bool
    {
        $supportsAttribute = in_array($attribute, [self::VIEW, self::EDIT, self::DELETE]);
        $supportsSubject = $subject instanceof Exchange;

        return $supportsAttribute && $supportsSubject;
    }

    /**
     * @param string $attribute
     * @param Exchange $exchange
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, $exchange, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $exchange is a Exchange object, thanks to `supports()`
        /** @var Exchange $Exchange */
        $Exchange = $exchange;

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
        return ($Exchange->getOwner() == $user || $Exchange->getProposer() == $user || $this->security->isGranted('ROLE_ADMIN'));
    }

    private function canEdit(Exchange $Exchange, User $user): bool
    {
        // this assumes that the Exchange object has a `getOwner()` method
        return ($Exchange->getOwner() == $user || $Exchange->getProposer() == $user || $this->security->isGranted('ROLE_ADMIN'));
    }

    private function canDelete(Exchange $Exchange, User $user): bool
    {
        // this assumes that the Exchange object has a `getOwner()` method
        return ($Exchange->getOwner() == $user || $Exchange->getProposer() == $user || $this->security->isGranted('ROLE_ADMIN'));
    }
}
