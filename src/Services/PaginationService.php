<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 *
 * Service for pagination helpers
 */

namespace App\Services;

use HugaShop\Services\Request;
use HugaShop\Models\Settings;

class PaginationService
{

    /**
     * Initialize pagination filter
     */
    public static function initFilter(?int $per_page = null): array
    {
        $per_page = $per_page ?: Settings::getParam('products_num_admin');

        return [
            'page'  => max(1, Request::getInt('page')),
            'limit' => Request::get('page', 'string') === 'all'
                ? 'all'
                : $per_page,
        ];
    }


    /**
     * Assign pagination data to template
     */
    public static function getPagination(int $items_count, array $filter)
    {
        $pagination = new \stdClass();
        $pagination->pages_count = ceil($items_count / max($filter['limit'], 1));
        $pagination->current_page = $filter['limit'] === 'all'
            ? 'all'
            : $filter['page'];
        return $pagination;
    }
}
