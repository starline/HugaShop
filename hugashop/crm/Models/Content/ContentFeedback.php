<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 */

namespace HugaShop\Models\Content;

use HugaShop\Models\BaseModel;

class ContentFeedback extends BaseModel
{

    protected static $table_fields = [
        'id' =>                 ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'name' =>               ['type' => 'varchar',       'req' => true],
        'date' =>               ['type' => 'tinyint',       'def' => 'CURRENT_TIMESTAMP'],
        'ip' =>                 ['type' => 'varchar',       'length' => 20],
        'email' =>              ['type' => 'varchar'],
        'message' =>            ['type' => 'text']
    ];


    /**
     * Добавляем Feedback
     * @param $feedback
     */
    public static function addFeedback(object $feedback)
    {
        $feedback->date = date("Y-m-d H:i:s");
        return ContentFeedback::createOne($feedback);
    }
}
