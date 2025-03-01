<?php

namespace App\Enum;

enum NotificationType: string
{
    case WELCOME_EMAIL = 'welcome_email';
    case ACCOUNT_DEACTIVATED = 'account_deactivated';
    case ACCOUNT_REACTIVATED = 'account_reactivated';

    public static function getNotificationTypes(): array
    {
        return [
            'Welcome Email' => self::WELCOME_EMAIL->value,
            'Account Deactivated' => self::ACCOUNT_DEACTIVATED->value,
            'Account Reactivated' => self::ACCOUNT_REACTIVATED->value,
        ];
    }
}
