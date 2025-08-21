<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\SubscribeOffer\Models;

use HugaShop\Extensions\BaseExtensionModel;

final class SubscribeOffer extends BaseExtensionModel
{
    public $timestamps = true;

    protected static $table_fields = [
        'id'         => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'email'      => ['type' => 'varchar', 'req' => true],
        'page'       => ['type' => 'varchar', 'access' => false],
        'user_agent' => ['type' => 'varchar', 'access' => false],
        'ip'         => ['type' => 'varchar', 'access' => false, 'length' => 20],
    ];
}
