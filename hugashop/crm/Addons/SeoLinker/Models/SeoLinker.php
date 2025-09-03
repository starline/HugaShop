<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 *
 */

namespace HugaShop\Addons\SeoLinker\Models;

use HugaShop\Addons\BaseAddonModel;

final class SeoLinker extends BaseAddonModel
{

    public $timestamps = true;
    protected static $table_fields = [
        'id'                => ['type' => 'int', 'extra' => 'AUTO_INCREMENT'],
        'url'               => ['type' => 'varchar'],
        'depth'             => ['type' => 'int', 'def' => 0],
        'out_internal'      => ['type' => 'int', 'def' => 0],
        'out_external'      => ['type' => 'int', 'def' => 0],
        'in_internal'       => ['type' => 'int', 'def' => 0],
        'meta_title'        => ['type' => 'varchar'],
        'meta_description'  => ['type' => 'varchar'],
        'h1'                => ['type' => 'varchar'],
        'scanned'           => ['type' => 'tinyint', 'def' => 0],
    ];
}
