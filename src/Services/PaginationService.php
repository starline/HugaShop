<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 *
 * Service for pagination helpers
 */

namespace App\Services;

use HugaShop\Models\Request;
use HugaShop\Models\Settings;

class PaginationService
{

    /**
     * Initialize pagination filter
     */
    public static function initFilter(): array
    {
        return [
            'page'  => max(1, Request::get('page', 'int')),
            'limit' => Request::get('page', 'string') === 'all'
                ? 'all'
                : Settings::getParam('products_num_admin'),
        ];
    }


    /**
     * Assign pagination data to template
     */
    public static function getPagination(int $itemsCount, array $filter)
    {
        $pagination = new \stdClass();
        $pagination->pages_count = ceil($itemsCount / max((int) Settings::getParam('products_num_admin'), 1));
        $pagination->current_page = $filter['limit'] === 'all'
            ? 'all'
            : $filter['page'];
        return $pagination;
    }
}
