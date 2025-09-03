<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Addons\ProductConfigurator\Models;

use HugaShop\Addons\BaseAddonModel;

final class ConfiguratorStep extends BaseAddonModel
{
    protected static $table_fields = [
        'id'              => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'configurator_id' => ['type' => 'int'],
        'name'            => ['type' => 'varchar', 'trans' => true, 'required' => true],
        'description'     => ['type' => 'text',    'trans' => true],
        'image'           => ['type' => 'varchar'],
        'position'        => ['type' => 'int',     'def' => 0],
    ];
}
