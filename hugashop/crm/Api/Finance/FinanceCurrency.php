<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.2
 * 
 * Use Cache
 *
 */

namespace HugaShop\Api\Finance;

use HugaShop\Api\Helper;
use HugaShop\Api\Request;
use HugaShop\Api\Settings;
use HugaShop\Api\BaseModel;

class FinanceCurrency extends BaseModel
{
    protected static $table_fields = [
        'id' =>                 ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'name' =>               ['type' => 'varchar',       'req' => true],
        'sign' =>               ['type' => 'varchar',       'lenght' => 20],
        'code' =>               ['type' => 'char',          'lenght' => 4],
        'rate_from' =>          ['type' => 'decimal',       'lenght' => 10.2,   'def' => 1.00],
        'rate_to' =>            ['type' => 'decimal',       'lenght' => 10.2,   'def' => 1.00],
        'cents' =>              ['type' => 'tinyint',       'lenght' => 1,      'def' => 2],
        'position' =>           ['type' => 'int',                               'def' => 0],
        'enabled' =>            ['type' => 'tinyint',       'def' => 0]
    ];

    private static $currencies = [];
    private static $main_currency;


    /**
     * Выбираем все валюты self::$currencies
     * Определяем оcновную валюту $this->main_currency
     */
    public static function initCurrencies()
    {

        // Cache
        $cache_item = Helper::cache()->getItem(Helper::class_basename(self::class));

        if (!$cache_item->isHit()) {

            // Выбираем из базы валюты
            $results = self::getList(order: ['position']);

            $currencies = [];
            foreach ($results as $c) {
                $currencies[$c->id] = $c;
            }

            Helper::cache()->save($cache_item->set($currencies));
        }

        self::$currencies = $cache_item->get();

        // Основная валюта
        self::$main_currency = reset(self::$currencies);
    }


    /**
     * Get currencies
     */
    public static function getCurrencies(array $filter = [])
    {
        if (!isset(self::$currencies)) {
            self::initCurrencies();
        }

        $currencies = [];
        foreach (self::$currencies as $id => $currency) {
            if ((isset($filter['enabled']) && $filter['enabled'] == 1 && $currency->enabled) || empty($filter['enabled'])) {
                $currencies[$id] = $currency;
            }
        }
        return $currencies;
    }


    /**
     * Get currency
     * @param int|string $id if NULL will return main currency
     */
    public static function getCurrency(int|string|null $id = null)
    {
        if (!isset(self::$currencies)) {
            self::initCurrencies();
        }

        // по id
        if (!empty($id) && is_numeric($id) && isset(self::$currencies[$id])) {
            return self::$currencies[$id];
        }

        // по ISO коду
        if (!empty($id) && is_string($id)) {
            foreach (self::$currencies as $currency) {
                if ($currency->code == $id) {
                    return $currency;
                }
            }
        }

        // Return main currency
        return self::getMainCurrency();
    }


    /**
     * Get main currency
     */
    public static function getMainCurrency()
    {
        if (!isset(self::$main_currency)) {
            self::initCurrencies();
        }

        return self::$main_currency;
    }


    /**
     * Добавляем валюту
     * @param $currency
     */
    public static function addCurrency($currency)
    {

        Helper::cache()->delete(Helper::class_basename(self::class)); # Cache clean

        $currency = self::create($currency);
        self::initCurrencies();
        return $currency->id;
    }


    /**
     * Update currency
     * @param int|array $id
     */
    public static function updateCurrency(int|array $id, $currency)
    {
        Helper::cache()->delete(Helper::class_basename(self::class)); # Cache clean
        $result = self::updateOne($id, $currency);
        self::initCurrencies();
        return $result;
    }


    /**
     * Delete currency
     * @param int $id
     */
    public static function deleteCurrency(int $id)
    {
        Helper::cache()->delete(Helper::class_basename(self::class)); # Cache clean
        $result = self::deleteOne($id);
        self::initCurrencies();
        return $result;
    }


    /**
     * Конвертируем в другую валюту
     * Установлен плагин в Smarty(class Design)
     * @param int|float|null $amount
     * @param int|string $currency_to_id - id валюты в которую перевести
     * @param bool $format - Форматировать цены соласно настройкам сайта
     * @param int|string $currency_from_id - id валюты с которой переводим. Если не задана, берется основная валюта
     */
    public static function priceConvert(int|float|null $amount, int|string|null $currency_to_id = null, bool $format = true, int|string|null $currency_from_id = null)
    {

        if (empty($amount)) {
            return 0;
        }

        // Выбираем данные по валюте
        if (!is_null($currency_to_id)) {
            $currency_to = self::getCurrency($currency_to_id);
        } elseif (!empty(Request::getSession('currency_id'))) {
            $currency_to = self::getCurrency(intval(Request::getSession('currency_id')));
        } else {
            $currency_to = self::getMainCurrency(); # Основная валюта
        }

        if (!is_null($currency_from_id)) {
            $currency_from = self::getCurrency($currency_from_id);
        }

        $result = $amount;

        // Переводим между валютами, кроме основной
        if (!empty($currency_from) && !empty($currency_to) && $currency_from->id != self::$main_currency->id) {

            // Если переводим любую валюту В основную валюту
            if ($currency_to->id == self::$main_currency->id) {
                $result = $amount * $currency_from->rate_to / $currency_from->rate_from;

                // Переводим между валютами
            } else {
                $result = $amount / $currency_from->rate_from * $currency_from->rate_to; # переводим в основную валюту
                $result = $result * $currency_to->rate_from / $currency_to->rate_to; # переводим в нужную валюту
            }

            // Переводим ИЗ основной валюты. Eсли $currency_from = основная валюта
        } elseif (!empty($currency_to) and $currency_to->id != self::$main_currency->id) {

            // Умножим на курс валюты
            $result = $amount * $currency_to->rate_from / $currency_to->rate_to;
        }

        // Точность отображения, знаков после запятой.
        // Показывать копейки или нет .00
        $precision = $currency_to->cents ? 2 : 0;

        // Eсть знаки после запятой, оставляем отображение их
        $result_arr = explode('.', strval($result));
        if (!empty($result_arr[1]) and $precision == 0) {

            // Oбрезаем до 2х знаков
            $precision = (strlen($result_arr[1]) > 0) ? 2 : 0;
        }

        // Форматирование цены. Задается в настройках сайта
        // Пример 1 234.56
        if ($format) {
            $result = Helper::numberFormat($result, $precision);
        } else {
            $result = round($result, $precision);
        }

        return $result;
    }


    /**
     * Форматируем показ цены html
     * Установлен плагин в Smarty(class Design)
     *
     * @param int|float $price
     * @param string $type
     *      null - Show clear price. without sign (+/-) and colorize
     *      profit - Show price with sign (+/-)
     *      color - Show colorize price
     *      no_currency - Don't show currency
     *      no_html - without HTML
     *      clean - without HTML and Currency
     * @param string $currency_code
     */
    public static function priceHTML(int|float|null $price, ?string $type = null, ?string $currency_code = null)
    {

        if (is_null($price)) {
            return;
        }

        $decimals_point =       Settings::getParam('decimals_point');
        $thousands_separator =  Settings::getParam('thousands_separator');
        $currency =             self::getCurrency($currency_code);
        $precision = 2; # .00

        // Show sign
        $sign_number = ($price <= 0) ? '' : (($type == 'profit') ? '+' : '');

        // Colorize
        $main_class = '';
        if (in_array($type, ['profit', 'color'])) {
            $main_class = ($price < 0) ? 'negative' : 'positive';
            $main_class = ($price == 0) ? 'zero' : $main_class;
            $main_class = ' ' . $main_class; # add space
        }

        $price_format = number_format($price, $precision, $decimals_point, $thousands_separator);
        $price_arr =    explode($decimals_point, $price_format);

        $result = '';

        // Price
        if (in_array($type, ['no_html', 'clean'])) {
            $result .= $sign_number . $price_arr[0];
        } else {
            $result = '<span class="price_html' . $main_class . '">';
            $result .= '<span class="price_main">' . $sign_number . $price_arr[0] . '</span>';
        }

        // Decimal
        $decimal_string = '';
        if ($price_arr[1] != '00' || $currency->cents) {
            $decimal_string = $decimals_point . $price_arr[1];
        }
        if (in_array($type, ['no_html', 'clean'])) {
            $result .= $decimal_string;
        } else {
            $result .= '<span class="price_decimal">' . $decimal_string . '</span>';
        }


        // Currency sign
        if (!in_array($type, ['no_currency', 'clean'])) {
            if ($type == 'no_html') {
                $result .= ' ' . $currency->sign;
            } else {
                $result .= '<span class="price_sign">' . $currency->sign . '</span>';
            }
        }

        if (!in_array($type, ['no_html', 'clean'])) {
            $result .= '</span>';
        }

        return $result;
    }
}
