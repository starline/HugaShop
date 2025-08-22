<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 */

namespace HugaShop\Addons\BookingShinomontag\Models;

use HugaShop\Addons\BaseAddonModel;

final class Booking extends BaseAddonModel
{

    public $timestamps = true;
    protected static $table_fields = [
        'id'      => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'date'    => ['type' => 'date',    'req' => true],
        'time'    => ['type' => 'time',    'req' => true],
        'name'    => ['type' => 'varchar', 'req' => true],
        'phone'   => ['type' => 'varchar', 'req' => true, 'length' => 32],
        'comment' => ['type' => 'text'],
    ];
}
