<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Addons\ProductConfigurator\Models;

use HugaShop\Addons\BaseAddonModel;

final class Configurator extends BaseAddonModel
{
    protected static $table_fields = [
        'id'          => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'name'        => ['type' => 'varchar', 'trans' => true, 'required' => true],
        'description' => ['type' => 'text',    'trans' => true],
        'position'    => ['type' => 'int',     'def' => 0],
        'enabled'     => ['type' => 'tinyint', 'def' => 1],
    ];
}
