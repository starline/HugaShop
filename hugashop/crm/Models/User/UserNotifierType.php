<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 *
 */

namespace HugaShop\Models\User;

use HugaShop\Models\BaseModel;

class UserNotifierType extends BaseModel
{
    protected static $table_fields = [
        'id' =>                     ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'user_id' =>                ['type' => 'int',           'req' => true],
        'notifier_id' =>            ['type' => 'int',           'req' => true],
        'type' =>                   ['type' => 'varchar']
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function notifier()
    {
        return $this->belongsTo(UserNotifier::class, 'notifier_id');
    }


    /**
     * Get notifier type for User
     * @param int $user_id
     */
    public static function getUserTypes(int $user_id)
    {
        $results =  self::query()->where('user_id', $user_id)->get();

        // Группировка по notifier_id
        return $results->groupBy('notifier_id')
            ->map(fn($items) => $items->pluck('type')->all())
            ->toArray();
    }


    /**
     * Update User Notifier messages Tupes
     * @param int $user_id
     * @param ?array $notifier_types
     */
    public static function updateTypes(int $user_id, ?array $notifier_types = []): bool
    {
        // Удаляем все старые записи
        self::deleteBy('user_id', $user_id);

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

            self::insert($insertData);
        }

        return true;
    }
}
