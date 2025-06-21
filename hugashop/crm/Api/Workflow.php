<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 *
 */

namespace HugaShop\Api;

use HugaShop\Api\BaseModel;
use HugaShop\Api\User\User;

class Workflow extends BaseModel
{

    protected static $table_fields = [
        'id' =>             ['type' => 'int',       'extra' => 'AUTO_INCREMENT'],
        'entity_id' =>      ['type' => 'int'],
        'entity_name' =>    ['type' => 'varchar'],
        'method' =>         ['type' => 'varchar'],
        'date' =>           ['type' => 'datetime',  'def' => 'CURRENT_TIMESTAMP'],
        'user_id' =>        ['type' => 'int'],
        'params' =>         ['type' => 'text']
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public static function addWorkflow($callable_name, $params)
    {

        if (empty(User::authUser('id')) || empty($params)) {
            return false;
        }

        $workflow = [];
        $workflow['user_id'] = intval(User::authUser('id'));

        // Exaple: HugaShop\\Api\\Product::updateProduct
        $callable_name = str_replace("HugaShop\\Api\\", "", $callable_name);
        $entity_method = explode('::', $callable_name);

        $workflow['entity_name'] = $entity_method[0];
        $workflow['method'] = $entity_method[1];
        $workflow['date'] = date("Y-m-d H:i:s"); # current date

        if (is_numeric($params[0])) {
            $workflow['entity_id'] = intval($params[0]);
        }
        if (isset($params[1])) {
            $workflow['params'] = json_encode($params[1]);
        }

        return Workflow::create($workflow);
    }
}
