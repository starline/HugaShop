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

final class SeoLinkerLink extends BaseExtensionModel
{
    protected static $table_fields = [
        'id'       => ['type' => 'int', 'extra' => 'AUTO_INCREMENT'],
        'from_url' => ['type' => 'varchar'],
        'to_url'   => ['type' => 'varchar'],
        'type'     => ['type' => 'varchar'],
    ];
}
