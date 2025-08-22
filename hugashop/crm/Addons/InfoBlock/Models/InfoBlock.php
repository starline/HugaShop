<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
 *
 */


namespace HugaShop\Addons\InfoBlock\Models;

use HugaShop\Addons\BaseAddonModel;

final class InfoBlock extends BaseAddonModel
{
    protected static $table_fields = [
        'id'       => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'name'     => ['type' => 'varchar', 'trans' => true, 'required' => 'true'],
        'body'     => ['type' => 'text',    'trans' => true],
        'position' => ['type' => 'int',     'def' => 0],
        'enabled'  => ['type' => 'tinyint', 'def' => 0],
    ];
}
