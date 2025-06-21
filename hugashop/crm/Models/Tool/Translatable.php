<?php

namespace HugaShop\Models\Traits;

use HugaShop\Models\Helper;

trait Translatable
{
    /**
     * Model class used for translations
     *
     * @var string
     */
    protected static string $translation_model;

    /**
     * Translation foreign key column
     * (defaults to snake case of model name + _id)
     *
     * @var string|null
     */
    protected static ?string $translation_foreign_key = null;

    /**
     * Get translation record for entity
     */
    public static function getTranslation(int $entity_id, string $code)
    {
        $model = static::getTranslationModel();
        $fk = static::getTranslationForeignKey();

        return $model::query()
            ->where($fk, $entity_id)
            ->where('language_code', $code)
            ->first();
    }

    /**
     * Update or create translation
     */
    public static function updateTranslation(int $entity_id, string $code, array $data)
    {
        $model = static::getTranslationModel();
        $fk = static::getTranslationForeignKey();

        return $model::query()->updateOrCreate(
            [$fk => $entity_id, 'language_code' => $code],
            $data
        );
    }

    /**
     * Fill entity with translated fields for provided language
     */
    public static function fillTranslation(object $entity, string $code): object
    {
        $translation = static::getTranslation($entity->id, $code);

        foreach (static::getTransFields() as $field) {
            $entity->$field = $translation->$field ?? null;
        }

        return $entity;
    }

    /**
     * Get translation model class name
     */
    protected static function getTranslationModel(): string
    {
        if (empty(static::$translation_model)) {
            throw new \LogicException('Translation model not defined');
        }

        return static::$translation_model;
    }

    /**
     * Get foreign key used in translation table
     */
    protected static function getTranslationForeignKey(): string
    {
        if (!empty(static::$translation_foreign_key)) {
            return static::$translation_foreign_key;
        }

        return Helper::camelToSnakeCase(Helper::class_basename(static::class)) . '_id';
    }
}
