<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
 *
 */

namespace HugaShop\Addons\SeoLinker\Models;

use HugaShop\Addons\BaseAddonModel;

final class SeoLinkerLink extends BaseAddonModel
{
    protected static $table_fields = [
        'id'       => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'from_url' => ['type' => 'varchar'],
        'to_url'   => ['type' => 'varchar'],
        'type'     => ['type' => 'varchar'],
        'nofollow' => ['type' => 'tinyint', 'def'  => 0],
        'status'   => ['type' => 'int', 'def'  => 200]      # HTTP status code
    ];
}
