<?php

namespace App\Security;

use App\Entity\BlogPost;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BlogVoter extends Voter
{
    const EDIT = 'edit';
    const DELETE = 'delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::EDIT, self::DELETE])) {
            return false;
        }

        if (!($subject instanceof BlogPost)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!($user instanceof User)) {
            return false;
        }

        $blogPost = $subject;

        return match($attribute) {
            self::EDIT, self::DELETE => $this->isOwner($blogPost, $user),
        };
    }

    private function isOwner(BlogPost $blogPost, User $user): bool
    {
        if ($blogPost->getAuthor() !== $user) {
            return false;
        }

        return true;
    }
}