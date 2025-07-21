<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 */

namespace App\Services;

use HugaShop\Services\Cache;
use HugaShop\Models\User\User;

class LockEditService
{

    private static $ttl = 600; # seconds

    /**
     * Get Entity key
     */
    private static function getKey(string $entity, int|string $id): string
    {
        return $entity . '_' . $id;
    }


    /**
     * Lock Entity
     */
    public static function lock(string $entity, int|string $id): bool
    {

        $cache      = Cache::cache(self::class, self::$ttl);
        $item       = $cache->getItem(self::getKey($entity, $id));
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
    public static function unlock(string $entity, int|string $id): void
    {
        $cache      = Cache::cache(self::class);
        $item       = $cache->getItem(self::getKey($entity, $id));
        $user_id    = User::authUser('id');

        if ($item->isHit()) {
            $lock = $item->get();
            if ($lock['user_id'] === $user_id) {
                $cache->deleteItem(self::getKey($entity, $id));
            }
        }
    }


    /**
     * Check if entity  is lockid
     */
    public static function isLocked(string $entity, int|string $id): ?int
    {
        $cache  = Cache::cache(self::class);
        $item   = $cache->getItem(self::getKey($entity, $id));

        if ($item->isHit()) {
            $lock = $item->get();
            if ($lock['expires_at'] > time()) {
                return (int) $lock['user_id'];
            }
        }
        return null;
    }
}
