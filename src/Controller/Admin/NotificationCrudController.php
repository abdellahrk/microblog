<?php

namespace App\Controller\Admin;

use App\Entity\Notification;
use App\Enum\NotificationType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class NotificationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Notification::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            ChoiceField::new('type')->setChoices(NotificationType::getNotificationTypes()),
            TextField::new('subject'),
            TextEditorField::new('content'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setHelp('new', 'if you want to include the fullName of the user in the email, write this tag: {{ fullName }} or {{ username }} for the username or {{ email }} for the user email')
            ;
    }
}
