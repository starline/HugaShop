<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 4.3
 *
 */

namespace HugaShop\Models\User;

use HugaShop\Models\BaseModel;
use HugaShop\Models\User\UserNotifierType;
use HugaShop\Services\Cache;

class UserNotifier extends BaseModel
{

    protected static $table_fields = [
        'id' =>             ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'name' =>           ['type' => 'varchar',       'req' => true],
        'comment' =>        ['type' => 'varchar'],
        'module' =>         ['type' => 'varchar'],
        'type' =>           ['type' => 'varchar'],
        'settings' =>       ['type' => 'text'],
        'position' =>       ['type' => 'int',           'def' => 0],
        'enabled' =>        ['type' => 'int',           'def' => 0],
    ];

    private static array $settings_cache = [];

    public function types()
    {
        return $this->hasMany(UserNotifierType::class, 'notifier_id');
    }


    public static function createOne(array|object $values): object
    {
        Cache::cache(self::class)->clear();
        self::$settings_cache = [];
        return parent::createOne($values);
    }


    public static function updateOne(int $id, array|object $values)
    {
        Cache::cache(self::class)->clear();
        self::$settings_cache = [];
        return parent::updateOne($id, $values);
    }


    /**
     * Delete Notifier method
     */
    public static function deleteNotifier($id)
    {
        Cache::cache(self::class)->clear();
        self::$settings_cache = [];
        UserNotifierType::where('notifier_id', $id)->delete();
        return self::deleteOne($id);
    }


    /**
     * Get Notifier Method settings
     * @param string $module_name
     */
    public static function getNotifierSettings(string $module_name): object
    {

        if (isset(self::$settings_cache[$module_name])) {
            return self::$settings_cache[$module_name];
        }

        $cache_item = Cache::cache(self::class)->getItem('settings_' . $module_name);
        if (!$cache_item->isHit()) {
            $record = self::query()
                ->where('module', $module_name)
                ->value('settings');

            $settings = empty($record) ? false : (object) unserialize($record);
            Cache::cache(self::class)->save($cache_item->set($settings));
        } else {
            $settings = $cache_item->get();
        }

        return self::$settings_cache[$module_name] = $settings;
    }


    /**
     * Get Allowed notifier type for User
     * @param int $user_id
     */
    public static function getAllowedNotifier(int $user_id, string $message_key)
    {
        return self::query()
            ->where('enabled', 1)
            ->whereHas('types', function ($sub_query) use ($user_id, $message_key) {
                $sub_query->where('user_id', $user_id)
                    ->where('type', $message_key);
            })
            ->get();
    }
}
