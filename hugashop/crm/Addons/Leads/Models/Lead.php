<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 *
 * Lead entity produced from incoming calls.
 */

namespace HugaShop\Addons\Leads\Models;

use HugaShop\Models\User\User;
use HugaShop\Addons\BaseAddonModel;

class Lead extends BaseAddonModel
{

    public $timestamps = true;
    protected static $table_fields = [
        'id' =>         ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'client_id' =>  ['type' => 'int',     'def' => 0],
        'phone' =>      ['type' => 'varchar', 'length' => 32, 'search' => true],
        'status' =>     ['type' => 'varchar', 'length' => 32, 'def' => 'new'],
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id', 'id');
    }

    public function calls()
    {
        return $this->hasMany(LeadCall::class, 'lead_id', 'id');
    }
}
