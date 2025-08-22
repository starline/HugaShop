<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 */

namespace HugaShop\Addons\HelpOffer\Models;

use HugaShop\Addons\BaseAddonModel;

final class HelpOffer extends BaseAddonModel
{

    public $timestamps = true;
    protected static $table_fields = [
        'id'         => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'name'       => ['type' => 'varchar', 'req' => true],
        'phone'      => ['type' => 'varchar', 'req' => true],
        'email'      => ['type' => 'varchar'],
        'page'       => ['type' => 'varchar', 'access' => false],
        'user_agent' => ['type' => 'varchar', 'access' => false],
        'ip'         => ['type' => 'varchar', 'access' => false, 'length' => 20],
    ];
}
