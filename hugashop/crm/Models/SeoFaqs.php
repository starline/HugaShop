<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.9
 *
 */

namespace HugaShop\Models;

class SeoFaqs extends BaseModel
{

    protected static $table_fields = [
        'id'           => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'name'         => ['type' => 'varchar'],
        'entity_name'  => ['type' => 'varchar'],
        'entity_id'    => ['type' => 'int'],
        'position'     => ['type' => 'int',     'def' => 0],
    ];


    /**
     * Catch faqs from POST
     * @param $entity_name category
     */
    public static function catchKeywords(int $entity_id, string $entity_name)
    {
        $seo_faqs = Request::post('seo_faqs');
        $seo_faqs_arr = preg_split('/\r\n|\r|\n/', $seo_faqs); # Формирум мосcив из строк
        SeoFaqs::updateFAQs($seo_faqs_arr, $entity_id, $entity_name);
    }

    /**
     * Get FAQs for entity.
     */
    public static function getFaqs(int $entity_id, string $entity_name): array
    {
        return self::query()
            ->where('entity_id', $entity_id)
            ->where('entity_name', $entity_name)
            ->orderBy('position')
            ->pluck('name')->toArray();
    }

    /**
     * Update FAQs for entity.
     */
    public static function updateFaqs(array $faqs, int $entity_id, string $entity_name): void
    {
        self::deleteFaqs($entity_id, $entity_name);

        foreach ($faqs as $position => $faq_name) {
            if (!empty($faq_name)) {
                $faq_model = new self();
                $faq_model->entity_id = $entity_id;
                $faq_model->entity_name = $entity_name;
                $faq_model->name = $faq_name;
                $faq_model->position = $position;
                $faq_model->save();
            }
        }
    }


    /**
     * Delete all FAQs for entity.
     */
    public static function deleteFaqs(int $entity_id, string $entity_name): void
    {
        self::query()
            ->where('entity_id', $entity_id)
            ->where('entity_name', $entity_name)
            ->delete();
    }
}
