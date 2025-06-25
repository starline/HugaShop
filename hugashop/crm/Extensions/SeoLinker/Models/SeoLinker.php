<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 */

namespace HugaShop\Extensions\SeoLinker\Models;

use HugaShop\Extensions\BaseExtensionModel;

final class SeoLinker extends BaseExtensionModel
{
    protected static $table_fields = [
        'id'            => ['type' => 'int', 'extra' => 'AUTO_INCREMENT'],
        'url'           => ['type' => 'varchar'],
        'depth'         => ['type' => 'int', 'def' => 0],
        'out_internal'  => ['type' => 'int', 'def' => 0],
        'out_external'  => ['type' => 'int', 'def' => 0],
        'in_internal'   => ['type' => 'int', 'def' => 0],
        'scanned'       => ['type' => 'tinyint', 'def' => 0],
    ];
}
