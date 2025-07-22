<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
 */

namespace App\Services;

use HugaShop\Services\Cache;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Models\User\User;

class LockEditService
{

    private static $ttl = 600; # seconds


    /**
     * Check entity edit lock for controller
     */
    public static function isEditLocked(string $model_name, ?int $item_id = null)
    {
        if (!$item_id) {
            return false;
        }

        $locked_key = self::getKey($model_name, $item_id);
        Design::assign('locked_key', $locked_key);

        $user_locked_id = self::isLocked($locked_key);
        if (!empty($user_locked_id) && $user_locked_id !== User::authUser('id')) {
            Design::assign('locked_user', User::getOne($user_locked_id));
            Design::append('service_messages_empty', 'entity_locked');
            return true;
        }

        return false;
    }


    /**
     * Lock Entity
     */
    public static function lock(string $locked_key): bool
    {

        $cache      = Cache::cache(self::class, self::$ttl);
        $item       = $cache->getItem($locked_key);
        $user_id    = User::authUser('id');

        if ($item->isHit()) {
            $lock = $item->get();
            if ($lock['user_id'] !== $user_id && $lock['expires_at'] > time()) {
                return false;
            }
        }

        $item->set([
            'user_id'   => $user_id,
            'expires_at' => time() + self::$ttl
        ]);

        $item->expiresAfter(self::$ttl);
        $cache->save($item);

        return true;
    }


    /**
     * Unlock Entity
     */
    public static function unlock(string $locked_key): void
    {
        $cache      = Cache::cache(self::class);
        $item       = $cache->getItem($locked_key);
        $user_id    = User::authUser('id');

        if ($item->isHit()) {
            $lock = $item->get();
            if ($lock['user_id'] === $user_id) {
                $cache->deleteItem($locked_key);
            }
        }
    }


    /**
     * Check if entity is lockid
     */
    public static function isLocked(string $locked_key): ?int
    {
        $cache  = Cache::cache(self::class);
        $item   = $cache->getItem($locked_key);

        if ($item->isHit()) {
            $lock = $item->get();
            if ($lock['expires_at'] > time()) {
                return (int) $lock['user_id'];
            }
        }
        return null;
    }


    /**
     * Get Entity key
     */
    private static function getKey(string $model_name, int|string $id): string
    {
        $key = Helper::makeToken($model_name);
        return $key . '.' . $id;
    }
}
