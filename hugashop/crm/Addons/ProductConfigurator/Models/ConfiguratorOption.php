<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 * 
 */

namespace HugaShop\Addons\ProductConfigurator\Models;

use HugaShop\Addons\BaseAddonModel;

final class ConfiguratorOption extends BaseAddonModel
{

    public $timestamps = true;
    protected static $table_fields = [
        'id'       => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'step_id'  => ['type' => 'int'],
        'name'     => ['type' => 'varchar', 'trans' => true, 'required' => true],
        'price'    => ['type' => 'decimal', 'length' => 14.2, 'def' => 0.00],
        'position' => ['type' => 'int',     'def' => 0],
    ];
}
