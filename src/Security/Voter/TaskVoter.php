<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskVoter extends Voter
{
    public const EDIT = 'POST_EDIT';
    public const VIEW = 'POST_VIEW';
    public const DELETE = 'POST_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof \App\Entity\Task;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // Si l'utilisateur n'est pas authentifié, ne pas accorder l'accès
        if (!$user instanceof UserInterface) {
            return false;
        }

        /* 
         * Si l'utilisateur a le rôle ROLE_ADMIN, il peut voir, éditer ou
         * supprimer la tâche liée à l'utilisateur "anonyme".
         */
        if (
            $subject->getUser()->getUsername() == 'anonyme' &&
            in_array('ROLE_ADMIN', $user->getRoles())
        ) {
            return true;
        } else {
            // Sinon, seul l'utilisateur lié à une tâche peut faire ces actions.
            return $user == $subject->getUser();
        }

        return false;
    }
}