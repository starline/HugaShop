<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
 * 
 * @link https://github.com/facebook/facebook-php-business-sdk
 * Composer require facebook/php-business-sdk
 *
 * Params
 * @link https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/server-event
 *
 * Request Helper
 * @link https://developers.facebook.com/docs/marketing-api/conversions-api/payload-helper?
 * 
 * Pixel Events
 * @link https://developers.facebook.com/docs/meta-pixel/reference
 *
 */

namespace HugaShop\Extensions\SeoPage\EventListener;

use FacebookAds\Api;
use App\Event\OrderAddEvent;
use HugaShop\Services\Request;
use FacebookAds\Logger\CurlLogger;
use HugaShop\Extensions\BaseExtension;
use FacebookAds\Object\ServerSide\Event;
use HugaShop\Models\Order\OrderPurchase;
use FacebookAds\Object\ServerSide\Content;
use FacebookAds\Object\ServerSide\UserData;
use HugaShop\Models\Finance\FinanceCurrency;
use FacebookAds\Object\ServerSide\CustomData;
use FacebookAds\Object\ServerSide\EventRequest;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class OrderAddListener extends BaseExtension
{


    /**
     * Order Add
     * @param OrderAddEvent $event
     */
    #[AsEventListener]
    public function onOrderAddEvent(OrderAddEvent $event): void
    {
        if (empty($this->settings->enabled)) {
            return;
        }

        $order = $event->getOrder();
        $purchases = OrderPurchase::getPurchases(['order_id' => $order->id], ['product']);

        // Should fill in value before running this script
        $access_token = $this->settings->api_token;
        $pixel_id = $this->settings->pixel_id;


        if (empty($access_token) || empty($pixel_id) || empty($purchases)) {
            return;
        }

        //  Include at least one user parameter for this event other than client_user_agent.
        if (empty($order->email) && empty($order->phone) && empty($order->name) && empty($order->ip)) {
            return;
        }


        $purchases_price = 0;
        $purchases_count = 0;
        $purchases_sku = [];
        $purchases_content = [];
        foreach ($purchases as $purch) {

            $purchases_sku[] = $purch->product->sku;
            $purchases_content[] = new Content(["product_id" => $purch->product->sku, "quantity" => $purch->amount, "item_price" => $purch->price]);

            // Общая стоимость товаров. Без учета скидок
            $purchases_price += $purch->price * $purch->amount;
            $purchases_count += $purch->amount;
        }

        // Initialize
        Api::init(null, null, $access_token);
        $api = Api::instance();
        $api->setLogger(new CurlLogger());
        $events = [];


        // User Data
        $user_data = new UserData();

        if (!empty($order->email)) {
            $user_data->setEmails([hash('sha256', strtolower($order->email))]);
        }
        if (!empty($order->phone)) {
            $user_data->setPhones([hash('sha256', strtolower($order->phone))]);
        }
        if (!empty($order->name)) {
            $user_data->setFirstNames([hash('sha256', strtolower($order->name))]);
        }

        // Customer made order
        if (!empty($order->ip)) {
            $user_data->setClientIpAddress($_SERVER['REMOTE_ADDR']);
            $user_data->setClientUserAgent($_SERVER['HTTP_USER_AGENT']);

            if ($fbc_cookie = Request::getCookie('_fbc', false)) {
                $user_data->setFbc($fbc_cookie);
            }

            if ($fbp_cookie = Request::getCookie('_fbp', false)) {
                $user_data->setFbp($fbp_cookie);
            }
        }


        // Product Data
        $custom_data = (new CustomData())
            ->setValue($purchases_price)
            ->setCurrency(FinanceCurrency::getMainCurrency()->code)
            ->setContentIds($purchases_sku)
            ->setContents($purchases_content)
            ->setContentType("product");

        $event = (new Event())
            ->setEventName("Purchase")
            ->setEventTime(time())
            ->setUserData($user_data)
            ->setCustomData($custom_data);

        // Customers order
        if (!empty($order->ip)) {
            $event->setActionSource("website");
        }

        // Manager made order
        else {
            $event->setActionSource("phone_call"); #referal type
        }

        array_push($events, $event);

        $request = (new EventRequest($pixel_id))
            ->setEvents($events);

        $request->execute();
    }
}
