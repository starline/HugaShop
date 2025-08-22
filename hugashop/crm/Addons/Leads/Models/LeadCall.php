<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 * Call history for leads.
 */

namespace HugaShop\Addons\Leads\Models;

use HugaShop\Addons\BaseAddonModel;

class LeadCall extends BaseAddonModel
{

    public $timestamps = true;
    protected static $table_fields = [
        'id' =>      ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'lead_id' => ['type' => 'int'],
        'phone' =>  ['type' => 'varchar', 'length' => 32],
        'type' =>   ['type' => 'varchar', 'length' => 16],
        'payload' => ['type' => 'text'],
    ];
}
