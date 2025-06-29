<?php


/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
 *
 */

namespace HugaShop\Models\Localization;

use HugaShop\Models\Design;
use HugaShop\Models\Request;
use HugaShop\Models\BaseModel;

class Language extends BaseModel
{

    protected static $table_fields = [
        'id' =>             ['type' => 'int',      'extra' => 'AUTO_INCREMENT'],
        'code' =>           ['type' => 'varchar'],
        'name' =>           ['type' => 'varchar'],
        'main' =>           ['type' => 'tinyint',  'def' => 0]
    ];

    public static $languages;
    public static $main_language;
    public static $current_language;


    /**
     * Get main language
     */
    public function main()
    {
        return $this->firstWhere('main', true);
    }

    /**
     * Get currentt language
     */
    public function current()
    {
        return $this->firstWhere('code', self::$current_language->code);
    }


    public static function getLanguages()
    {
        return self::$languages = self::query()->orderBy('id')->get();
    }


    public static function deleteLenguage(int $language_id)
    {
        $language = self::query()->where('id', $language_id)->first();
        if (empty($language)) {
            return false;
        }

        // If language is set as main, do not delete
        if (!empty($language->main)) {
            return false;
        }

        return self::deleteOne($language_id);
    }

    /**
     * Create language. If it's marked as main, remove main flag from others
     */
    public static function createOne(array|object $values): object
    {
        $vals = is_object($values) ? (array) $values : $values;
        if (!empty($vals['main'])) {

            // Reset main flag for other languages
            self::query()->where('main', 1)->update(['main' => 0]);
        }

        return parent::create($values);
    }

    /**
     * Update language. If it's marked as main, remove main flag from others
     */
    public static function updateOne(int|array $ids, array|object $values)
    {
        $vals = is_object($values) ? (array) $values : $values;
        if (!empty($vals['main'])) {
            $ids_array = is_array($ids) ? (array) ($ids['id'] ?? $ids) : [$ids];
            self::query()
                ->where('main', 1)
                ->whereNotIn('id', $ids_array)
                ->update(['main' => 0]);
        }

        return parent::updateOne($ids, $values);
    }


    /**
     * Init content language
     */
    public static function languageCatch()
    {
        self::$languages = Language::All();
        self::$main_language = self::$languages->firstWhere('main', 1);
        $language_code = Request::get('lang', 'string');
        self::$current_language = self::$languages->firstWhere('code', $language_code) ?: self::$main_language;

        Design::assign('languages', self::$languages);
        Design::assign('main_language', self::$main_language);
        Design::assign('current_language', self::$current_language);

        return self::$current_language;
    }


    /**
     * Check if language is defined
     */
    public static function checkOrGetCode()
    {
        if (
            !empty(self::$current_language) &&
            !empty(self::$main_language) &&
            self::$current_language->code !== self::$main_language->code
        ) {
            return self::$current_language->code;
        }

        return false;
    }
}
