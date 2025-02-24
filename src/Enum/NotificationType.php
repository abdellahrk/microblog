<?php

namespace App\Enum;

enum NotificationType: string
{
    case WELCOME_EMAIL = 'welcome_email';

    public static function getNotificationTypes(): array
    {
        return [
            'Welcome Email' => self::WELCOME_EMAIL->value,
        ];
    }
}
