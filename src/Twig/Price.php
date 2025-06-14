<?php

namespace App\Twig;

use Twig\TwigFilter;
use HugaShop\Api\Finance\FinanceCurrency;
use Twig\Extension\AbstractExtension;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(lazy: true)]
class Price extends AbstractExtension
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
