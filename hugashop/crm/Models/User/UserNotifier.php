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
        'settings' =>       ['type' => 'varchar'],
        'position' =>       ['type' => 'int',           'def' => 0],
        'enabled' =>        ['type' => 'int',           'def' => 0],
    ];

    private static array $settings_cache = [];

    public function types()
    {
        return $this->hasMany(UserNotifierType::class, 'notifier_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_notifier_type', 'notifier_id', 'user_id')
            ->withPivot('type');
    }

    public static function createOne(array|object $values): object
    {
        $notifier = parent::createOne($values);

        Cache::cache(self::class)->clear();
        self::$settings_cache = [];

        return $notifier;
    }

    public static function updateOne(int $id, array|object $values)
    {
        $result = parent::updateOne($id, $values);

        Cache::cache(self::class)->clear();
        self::$settings_cache = [];

        return $result;
    }


    /**
     * Delete Notifier method
     */
    public static function deleteNotifier($id)
    {
        UserNotifierType::where('notifier_id', $id)->delete();
        return self::deleteOne($id);
    }


    /**
     * Get Notifier Method settings
     * @param int|string $method_id
     * @return object
     */
    public static function getNotifierSettings(int|string $id): object|bool
    {
        $key = is_int($id) ? $id : $id;

        if (isset(self::$settings_cache[$key])) {
            return self::$settings_cache[$key];
        }

        $cache_item = Cache::cache(self::class)->getItem('settings_' . $key);
        if (!$cache_item->isHit()) {
            $query = UserNotifier::query();

            if (is_int($id)) {
                $query->where('id', $id);
            } else {
                $query->where('module', $id);
            }

            $record = $query->value('settings');

            $settings = empty($record) ? false : (object) unserialize($record);
            Cache::cache(self::class)->save($cache_item->set($settings));
        } else {
            $settings = $cache_item->get();
        }

        return self::$settings_cache[$key] = $settings;
    }


    /**
     * Update User Notifier messages Tupes
     * @param int $user_id
     * @param ?array $notifier_types
     */
    public static function updateUserNotifierTypes(int $user_id, ?array $notifier_types = null): bool
    {
        // Удаляем все старые записи
        UserNotifierType::query()->where('user_id', $user_id)->delete();

        // Вставляем новые, если есть
        if (!empty($notifier_types) && is_array($notifier_types)) {
            $insertData = [];

            foreach ($notifier_types as $notifier_id => $types) {
                foreach ($types as $type) {
                    if (!empty($type)) {
                        $insertData[] = [
                            'user_id' => $user_id,
                            'notifier_id' => $notifier_id,
                            'type' => $type,
                        ];
                    }
                }
            }

            if (!empty($insertData)) {
                UserNotifierType::insert($insertData);
            }
        }

        return true;
    }


    /**
     * Get notifier type for User
     * @param int $user_id
     * @param ?string $type
     */
    public static function getUserNotifierTypes(int $user_id, ?string $type = null): array
    {
        $query = UserNotifierType::query()
            ->where('user_id', $user_id);

        if (!empty($type)) {
            $query->where('type', $type);
        }

        $results = $query->get();

        // Группировка по notifier_id
        return $results->groupBy('notifier_id')
            ->map(fn($items) => $items->pluck('type')->all())
            ->toArray();
    }
}
