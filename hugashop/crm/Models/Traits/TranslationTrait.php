<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
 *
 */

namespace HugaShop\Models\Traits;

use HugaShop\Models\Localization\AbstractTranslation;

trait TranslationTrait
{


    /**
     * Check if Model has translate
     */
    public static function isTranslatable()
    {
        foreach (static::getFields() as $f => $options) {
            if (!empty($options['trans'])) {
                return true;
            }
        }
    }


    public static function separateValues(array|object $entity, string $language_code)
    {
        $translation_data = [];
        foreach (self::getTranslatableFields() as $field) {
            if (is_object($entity) && property_exists($entity, $field)) {
                $translation_data[$field] = $entity->$field;
                unset($entity->$field);
            } elseif (is_array($entity) && array_key_exists($field, $entity)) {
                $translation_data[$field] = $entity[$field];
                unset($entity[$field]);
            }
        }

        if (!empty($translation_data)) {
            self::updateTranslation($entity->id, $language_code, $translation_data);
        }

        return $entity;
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


    /**
     * Get translation record for entity
     */
    public static function getTranslation(int $entity_id, string $code)
    {
        $model = AbstractTranslation::setTableTranslation(static::class);
        $query = $model->newQuery();
        $query->where('entity_id', $entity_id)
            ->where('language_code', $code);

        return $model->runWithInitTable(function () use ($query) {
            return $query->first();
        });
    }


    /**
     * Update or create translation
     */
    public static function updateTranslation(int $entity_id, string $code, array $data)
    {
        $model = AbstractTranslation::setTableTranslation(static::class);
        $query = $model->newQuery();
        return $model->runWithInitTable(function () use ($query, $entity_id, $code, $data) {
            return $query->updateOrCreate(
                ['entity_id' => $entity_id, 'language_code' => $code],
                $data
            );
        });
    }
}
