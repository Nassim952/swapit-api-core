<?php
namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Message;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class MessageVoter extends Voter
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
        $supportsSubject = $subject instanceof Message;

        return $supportsAttribute && $supportsSubject;
    }

    /**
     * @param string $attribute
     * @param Message $message
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, $message, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $message is a Message object, thanks to `supports()`
        /** @var Message $message */
        $message = $message;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($message, $user);
            case self::EDIT:
                return $this->canEdit($message, $user);
            case self::DELETE:
                return $this->canDelete($message, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Message $message, User $user): bool
    {
        // if they can edit, they can view
        if ($this->canEdit($message, $user)) {
            return true;
        }
        // the Message object could have, for example, a method `isPrivate()`
        return ($message->getChannel()->getSender() == $user || $message->getChannel()->getReceiver() == $user || $this->security->isGranted('ROLE_ADMIN'));
    }

    private function canEdit(Message $message, User $user): bool
    {
        // this assumes that the Message object has a `getOwner()` method
        return ($message->getAuthor() == $user || $this->security->isGranted('ROLE_ADMIN'));
    }

    private function canDelete(Message $message, User $user): bool
    {
        // this assumes that the Message object has a `getOwner()` method
        return ($message->getAuthor() == $user || $this->security->isGranted('ROLE_ADMIN'));
    }
}
