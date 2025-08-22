<?php

namespace App\Twig;

use Twig\TwigFilter;
use HugaShop\Models\User\UserPermission;
use Twig\Addon\AbstractAddon;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(lazy: true)]
class UserAccess extends AbstractAddon
{


    /**
     * Use: {{ 'order'|user_access }}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('user_access', [UserPermission::class, 'checkAccess'], ['is_safe' => ['html']]),
        ];
    }
}
