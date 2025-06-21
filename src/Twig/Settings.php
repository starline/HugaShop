<?php

namespace App\Twig;

use Twig\TwigFunction;
use HugaShop\Models\Settings as SettingsApi;
use Twig\Extension\AbstractExtension;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(lazy: true)]
class Settings extends AbstractExtension
{

    /**
     * Use: {{ settings('theme') }}
     * ['is_safe' => ['html'] - filter is "safe" and doesn't need escaping
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('settings', [SettingsApi::class, 'getParam'], ['is_safe' => ['html']]),
        ];
    }
}
