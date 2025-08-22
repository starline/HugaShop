<?php

namespace App\Twig;

use Twig\TwigFilter;
use HugaShop\Models\Finance\FinanceCurrency;
use Twig\Addon\AbstractAddon;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(lazy: true)]
class Price extends AbstractAddon
{


    /**
     * Use: {{ float|price }}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('price', [FinanceCurrency::class, 'priceHTML'], ['is_safe' => ['html']]),
        ];
    }
}
