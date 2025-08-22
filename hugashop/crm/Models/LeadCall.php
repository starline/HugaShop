<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 *
 * Call history for leads.
 */

namespace HugaShop\Models;

class LeadCall extends BaseModel
{
    public $timestamps = true;

    protected static $table_fields = [
        'id' =>      ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'lead_id' => ['type' => 'int'],
        'phone' =>  ['type' => 'varchar', 'length' => 32],
        'type' =>   ['type' => 'varchar', 'length' => 16],
        'payload' =>['type' => 'text'],
    ];
}

