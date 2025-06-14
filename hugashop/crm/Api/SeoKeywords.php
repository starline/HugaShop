<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.8
 *
 */

namespace HugaShop\Api;

class SeoKeywords extends BaseModel
{

    public static $table_fields = [
        'id'           => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'name'         => ['type' => 'varchar'],
        'entity_name'  => ['type' => 'varchar'],
        'entity_id'    => ['type' => 'int'],
        'position'     => ['type' => 'int',     'def' => 0],
    ];


    /**
     * Catch kewords from POST
     * @param $entity_name category
     */
    public static function catchKeywords(int $entity_id, string $entity_name)
    {
        $seo_keywords = Request::post('seo_keywords');
        $seo_keywords_arr = preg_split('/\r\n|\r|\n/', $seo_keywords); # Формирум мосcив из строк
        SeoKeywords::updateKeywords($seo_keywords_arr, $entity_id, $entity_name);
    }


    /**
     * Get keywords by entity.
     */
    public static function getKeywords(int $entity_id, string $entity_name): array
    {
        return self::query()
            ->where('entity_id', $entity_id)
            ->where('entity_name', $entity_name)
            ->orderBy('position')
            ->pluck('name')->toArray();
    }


    /**
     * Update keywords for a specific entity.
     */
    public static function updateKeywords(array $keywords, int $entity_id, string $entity_name): void
    {
        self::deleteKeywords($entity_id, $entity_name);

        foreach ($keywords as $position => $keyword) {
            if (!empty($keyword)) {
                $keyword_model = new self();
                $keyword_model->entity_id = $entity_id;
                $keyword_model->entity_name = $entity_name;
                $keyword_model->name = $keyword;
                $keyword_model->position = $position;
                $keyword_model->save();
            }
        }
    }


    /**
     * Delete all keywords for a given entity.
     */
    public static function deleteKeywords(int $entity_id, string $entity_name): void
    {
        self::query()
            ->where('entity_id', $entity_id)
            ->where('entity_name', $entity_name)
            ->delete();
    }
}
