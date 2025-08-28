<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.8
 *
 */

namespace HugaShop\Models\Traits;

use HugaShop\Models\Localization\Language;
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


    /**
     * Separate and save traslated params
     */
    public static function separateTranslationData(array|object $entity, string $language_code)
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
            self::updateOrCreateTranslation($entity->id, $language_code, $translation_data);
        }

        return $entity;
    }


    /**
     * Fill entity with translated fields for provided language
     */
    public static function fillTranslation(object $entity, string $code, bool $merge_fields = false): object
    {
        $translation = static::getTranslation($entity->id, $code);
        foreach (static::getTranslatableFields() as $field) {
            if ($merge_fields === true) {
                if (!empty($translation->$field)) {
                    $entity->$field = $translation->$field;
                }
            } else {
                $entity->$field = $translation->$field ?? null;
            }
        }
        return $entity;
    }


    /**
     * Fill list of entities with translations using one DB query
     */
    public static function fillTranslations(iterable $entities, string $code, bool $merge_fields = false): iterable
    {
        $ids = [];
        foreach ($entities as $entity) {
            if (!empty($entity->id)) {
                $ids[] = $entity->id;
            }
        }

        if (empty($ids)) {
            return $entities;
        }

        $model = AbstractTranslation::setTableTranslation(static::class);
        $translations = $model->runWithInitTable(function () use ($model, $ids, $code) {
            return $model->newQuery()
                ->where('language_code', $code)
                ->whereIn('entity_id', $ids)
                ->get()
                ->keyBy('entity_id');
        });

        $fields = static::getTranslatableFields();
        foreach ($entities as $entity) {
            $translation = $translations[$entity->id] ?? null;
            foreach ($fields as $field) {
                if ($merge_fields === true) {
                    if (!empty($translation->$field)) {
                        $entity->$field = $translation->$field;
                    }
                } else {
                    $entity->$field = $translation->$field ?? null;
                }
            }
        }

        return $entities;
    }


    /**
     * Get translation record for entity
     */
    public static function getTranslation(int $entity_id, string $code)
    {
        $model = AbstractTranslation::setTableTranslation(static::class);

        return $model->runWithInitTable(function () use ($model, $entity_id, $code) {
            return $model->newQuery()
                ->where('entity_id', $entity_id)
                ->where('language_code', $code)
                ->first();
        });
    }


    /**
     * Get All translation record for entity
     */
    public static function getAllTranslations(int $entity_id)
    {
        $model = AbstractTranslation::setTableTranslation(static::class);

        return $model->runWithInitTable(function () use ($model, $entity_id) {
            return $model->newQuery()
                ->where('entity_id', $entity_id)
                ->get()->keyBy('language_code');
        });
    }


    /**
     * Update or create translation
     */
    public static function updateOrCreateTranslation(int $entity_id, string $code, array $data)
    {
        $model = AbstractTranslation::setTableTranslation(static::class);

        return $model->runWithInitTable(function () use ($model, $entity_id, $code, $data) {
            return $model->newQuery()->updateOrCreate(
                ['entity_id' => $entity_id, 'language_code' => $code],
                $data
            );
        });
    }


    /**
     * Delete translations for provided entity IDs
     */
    public static function deleteTranslations(int|array $entity_ids)
    {
        $ids = (array) $entity_ids;
        if (empty($ids)) {
            return 0;
        }

        $model = AbstractTranslation::setTableTranslation(static::class);

        return $model->runWithInitTable(function () use ($model, $ids) {
            return $model->newQuery()
                ->whereIn('entity_id', $ids)
                ->delete();
        });
    }


    /**
     * Get Entity with all traslated fields 
     */
    public static function getOneEditTranslate(int|array $id, array|string $join = [])
    {
        $result = static::getOne($id, $join);

        if ($language_code = Language::checkOrGetCode() and static::isTranslatable()) {
            $result = static::fillTranslation($result, $language_code, merge_fields: false);
        }
        return $result;
    }


    /**
     * Get Entity with only traslated fields 
     */
    public static function getOneTranslate(int|array $id, array|string $join = [])
    {

        // TODO caching
        $result = self::getOne($id, $join);

        if ($language_code = Language::checkOrGetCode() and static::isTranslatable() and $result) {
            $result = static::fillTranslation($result, $language_code, merge_fields: true);
        }
        return $result;
    }


    /**
     * Get Entities with all traslated fields 
     */
    public static function getListEditTranslate(array $filter = [], array|string $order = [], array|string $join = [], ?string $select = null, ?int $cache = 0)
    {

        // TODO caching
        $result = self::getList($filter, $order, $join, select: $select, cache: $cache);

        if ($language_code = Language::checkOrGetCode() and static::isTranslatable() and $result) {
            $result = static::fillTranslations($result, $language_code, merge_fields: false);
        }
        return $result;
    }


    /**
     * Get Entities with merge translated fields
     */
    public static function getListTranslate(array $filter = [], array|string $order = [], array|string $join = [], ?string $select = null, ?int $cache = 0)
    {

        // TODO caching
        $result = self::getList($filter, $order, $join, select: $select, cache: $cache);

        if ($language_code = Language::checkOrGetCode() and static::isTranslatable() and $result) {
            $result = static::fillTranslations($result, $language_code, merge_fields: true);
        }
        return $result;
    }
}
