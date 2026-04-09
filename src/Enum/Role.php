<?php

namespace App\Enum;

enum Role: string
{
    case ADMIN_RH = 'ADMIN_RH';
    case EMPLOYE = 'EMPLOYE';
    case FREELANCER = 'FREELANCER';

    public function getSecurityRole(): string
    {
        return match($this) {
            self::ADMIN_RH => 'ROLE_ADMIN',
            self::EMPLOYE => 'ROLE_EMPLOYE',
            self::FREELANCER => 'ROLE_FREELANCER',
        };
    }


}