<?php

namespace App\Service;

class GravatarService
{
    private const BASE_URL = 'https://www.gravatar.com/avatar/';
    private const DEFAULT_STYLE = 'identicon';

    public function getAvatarUrl(?string $email, int $size = 80): string
    {
        $hash = md5(strtolower(trim($email ?? '')));

        return self::BASE_URL . $hash . '?' . http_build_query([
            'd' => self::DEFAULT_STYLE,
            's' => $size,
        ]);
    }
}
