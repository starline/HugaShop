<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 */

namespace HugaShop\Addons\RedirectUrl\Models;

use HugaShop\Addons\BaseAddonModel;

final class RedirectUrl extends BaseAddonModel
{
    protected static $table_fields = [
        'id'            => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'url'           => ['type' => 'varchar', 'req' => true],
        'redirect'      => ['type' => 'varchar', 'req' => true],
        'enabled'       => ['type' => 'tinyint', 'def' => 1],
        'comment'       => ['type' => 'varchar'],
        'transitions'   => ['type' => 'int',     'def' => 0],
    ];
}
