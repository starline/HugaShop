<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.5
 *
 */

namespace HugaShop\Models\Traits;

use HugaShop\Models\Localization\AbstractTranslation;

trait Translatable
{

    /**
     * Get translation record for entity
     */
    public static function getTranslation(int $entity_id, string $code)
    {
        return AbstractTranslation::setTableTranslation(static::class)
            ->where('entity_id', $entity_id)
            ->where('language_code', $code)
            ->first();
    }


    /**
     * Update or create translation
     */
    public static function updateTranslation(int $entity_id, string $code, array $data)
    {

        return AbstractTranslation::setTableTranslation(static::class)
            ->updateOrCreate(
                ['entity_id' => $entity_id, 'language_code' => $code],
                $data
            );
    }


    /**
     * Fill entity with translated fields for provided language
     */
    public static function fillTranslation(object $entity, string $code): object
    {
        $translation = static::getTranslation($entity->id, $code);

        foreach (static::getTranslatableFields() as $field) {
            $entity->$field = $translation->$field ?? null;
        }

        return $entity;
    }
}
