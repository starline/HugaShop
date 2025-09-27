<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 * 
 * @link https://support.google.com/merchants/answer/14629205?sjid=3111895555780829847-NC
 *
 */

namespace HugaShop\Addons\GoogleCustomerReviews;

use DateTimeImmutable;
use HugaShop\Addons\BaseAddon;
use HugaShop\Services\Design;

final class GoogleCustomerReviews extends BaseAddon
{

    /**
     * Render Google Customer Reviews opt-in block for the front body.
     */
    public static function getFrontBodyTemplate(): ?string
    {
        $settings = self::getSettings();

        if (empty($settings->enabled) || empty($settings->merchant_id) || empty($settings->delivery_country) || empty($settings->delivery_days)) {
            return null;
        }

        $settings->delivery_country = strtoupper((string) $settings->delivery_country);
        $settings->delivery_days = max(0, (int) $settings->delivery_days);

        $order = Design::getSmarty()->getTemplateVars('order');
        $purchases = Design::getSmarty()->getTemplateVars('purchases');

        $estimated_delivery_date = (new DateTimeImmutable())
            ->modify(sprintf('+%d day', $settings->delivery_days))
            ->format('Y-m-d');

        if (!empty($order) && !empty($order->date)) {
            $order_date = new DateTimeImmutable($order->date);
            $estimated_delivery_date = $order_date
                ->modify(sprintf('+%d day', $settings->delivery_days))
                ->format('Y-m-d');
        }

        $gtins = [];
        if (!empty($purchases) && is_iterable($purchases)) {
            foreach ($purchases as $purchase) {
                $gtin = null;
                if (!empty($purchase->product?->gtin)) {
                    $gtin = $purchase->product->gtin;
                } elseif (!empty($purchase->gtin)) {
                    $gtin = $purchase->gtin;
                }

                if (!empty($gtin)) {
                    $gtins[] = (string) $gtin;
                }
            }
        }

        $data = [
            'merchant_id' => $settings->merchant_id,
            'order_id' => !empty($order?->id) ? (string) $order->id : '',
            'email' => !empty($order?->email) ? (string) $order->email : '',
            'delivery_country' => $settings->delivery_country,
            'estimated_delivery_date' => $estimated_delivery_date,
        ];

        if (!empty($gtins)) {
            $data['products'] = array_map(static function (string $gtin): array {
                return ['gtin' => $gtin];
            }, $gtins);
        }

        Design::assign('GoogleCustomerReviewsData', $data);

        return self::fetchTemplate('opt_in.tpl');
    }
}
