<?php
namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Channel;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ChannelVoter extends Voter
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
        $supportsSubject = $subject instanceof Channel;

        return $supportsAttribute && $supportsSubject;
    }

    /**
     * @param string $attribute
     * @param Channel $channel
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, $channel, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $channel is a Channel object, thanks to `supports()`
        /** @var Channel $channel */
        $channel = $channel;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($channel, $user);
            case self::EDIT:
                return $this->canEdit($channel, $user);
            case self::DELETE:
                return $this->canDelete($channel, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Channel $channel, User $user): bool
    {
        // if they can edit, they can view
        if ($this->canEdit($channel, $user)) {
            return true;
        }
        // the Channel object could have, for example, a method `isPrivate()`
        return ($channel->getSender() == $user || $channel->getReceiver() == $user || $this->security->isGranted('ROLE_ADMIN'));
    }

    private function canEdit(Channel $channel, User $user): bool
    {
        // this assumes that the Channel object has a `getOwner()` method
        return ($channel->getSender() == $user || $channel->getReceiver() == $user || $this->security->isGranted('ROLE_ADMIN'));
    }

    private function canDelete(Channel $channel, User $user): bool
    {
        // this assumes that the Channel object has a `getOwner()` method
        return ($channel->getSender() == $user || $channel->getReceiver() == $user || $this->security->isGranted('ROLE_ADMIN'));
    }
}
