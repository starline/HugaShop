<?php

namespace HugaShop\Extensions\InfoBlock\Model;

use HugaShop\Extensions\BaseExtensionModel;
use HugaShop\Api\Helper;

final class InfoBlock extends BaseExtensionModel
{
    public static $table_fields = [
        'id'       => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'name'     => ['type' => 'varchar', 'required' => 'true'],
        'body'     => ['type' => 'text'],
        'position' => ['type' => 'int',     'def' => 0],
        'enabled'  => ['type' => 'tinyint', 'def' => 0],
    ];

    public static function updateOne($id, $entity)
    {
        Helper::cache(self::class)->clear();
        return parent::updateOne($id, $entity);
    }

    public static function deleteOne($id)
    {
        Helper::cache(self::class)->clear();
        return parent::deleteOne($id);
    }
}
