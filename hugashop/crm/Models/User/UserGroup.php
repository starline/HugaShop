<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.3
 *
 */

namespace HugaShop\Models\User;

use HugaShop\Models\BaseModel;

class UserGroup extends BaseModel
{

    public $timestamps = false;
    protected static $table_fields = [
        'id' =>             ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'name' =>           ['type' => 'varchar',       'req' => true],
        'discount' =>       ['type' => 'decimal',       'length' => 10.2, 'def' => 0.00],
        'position' =>       ['type' => 'int',           'def' => 0]
    ];

    protected static $table_keys = [
        'position'      => ['column' => ['position', 'id'],    'type' => 'index']
    ];


    /**
     * Delete group
     * @param int $id
     */
    protected function deleteGroup(int $id)
    {
        User::where('group_id', $id)->update(['group_id' => null]);
        return UserGroup::deleteOne($id);
    }
}
