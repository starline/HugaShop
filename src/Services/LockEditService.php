<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace App\Services;

use HugaShop\Services\Cache;

class LockEditService
{

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
    public static function lock(string $entity, int|string $id, int $userId, int $ttl = 600): bool
    {
        $cache = Cache::cache(self::class, $ttl);
        $item = $cache->getItem(self::getKey($entity, $id));

        if ($item->isHit()) {
            $lock = $item->get();
            if ($lock['user_id'] !== $userId && $lock['expires_at'] > time()) {
                return false;
            }
        }

        $item->set([
            'user_id'   => $userId,
            'expires_at' => time() + $ttl
        ]);
        $item->expiresAfter($ttl);
        $cache->save($item);

        return true;
    }


    /**
     * Unlock Entity
     */
    public static function unlock(string $entity, int|string $id, int $userId): void
    {
        $cache = Cache::cache(self::class);
        $item = $cache->getItem(self::getKey($entity, $id));
        if ($item->isHit()) {
            $lock = $item->get();
            if ($lock['user_id'] === $userId) {
                $cache->deleteItem(self::getKey($entity, $id));
            }
        }
    }


    /**
     * Check if entity  is lockid
     */
    public static function isLocked(string $entity, int|string $id): ?int
    {
        $cache = Cache::cache(self::class);
        $item = $cache->getItem(self::getKey($entity, $id));
        if ($item->isHit()) {
            $lock = $item->get();
            if ($lock['expires_at'] > time()) {
                return (int) $lock['user_id'];
            }
        }
        return null;
    }
}
