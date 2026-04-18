<?php

namespace App\Twig;

use App\Service\GravatarService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GravatarExtension extends AbstractExtension
{
    public function __construct(
        private readonly GravatarService $gravatarService,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('gravatar', [$this->gravatarService, 'getAvatarUrl']),
        ];
    }
}
