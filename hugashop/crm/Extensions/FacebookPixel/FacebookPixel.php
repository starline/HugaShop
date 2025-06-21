<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.7
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

namespace HugaShop\Extensions\FacebookPixel;

use FacebookAds\Api;
use HugaShop\Models\Config;
use HugaShop\Models\Request;
// Symfony
use App\Event\CartAddEvent;
use HugaShop\Models\User\User;
use App\Event\OrderAddEvent;
// Facebook Bussines
use HugaShop\Models\Product\Product;
use FacebookAds\Logger\CurlLogger;
use App\Event\DesignBeforeFetchEvent;
use HugaShop\Models\Order\OrderPurchase;
use HugaShop\Extensions\BaseExtension;
use FacebookAds\Object\ServerSide\Event;
use HugaShop\Models\Finance\FinanceCurrency;
use FacebookAds\Object\ServerSide\Content;
use FacebookAds\Object\ServerSide\UserData;
use FacebookAds\Object\ServerSide\CustomData;
use FacebookAds\Object\ServerSide\EventRequest;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;


final class FacebookPixel extends BaseExtension
{
    /**
     * Get block template
     */
    public function getFrontHeadTemplate()
    {
        if (!empty($this->ext_settings->enabled)) {

            // Set currency
            if (empty($this->ext_settings->currency_code)) {
                $this->ext_settings->currency_code = FinanceCurrency::getMainCurrency()->code;
            }
            return $this->fetchTemplate('pixel.tpl');
        }
        return;
    }


    /**
     * Cart Add
     * @param CartAddEvent $event
     */
    #[AsEventListener]
    public function onCartAddEvent(CartAddEvent $event): void
    {
        if (empty($this->ext_settings->enabled)) {
            return;
        }

        $item = $event->getItem();

        // Should fill in value before running this script
        $access_token = $this->ext_settings->api_token;
        $pixel_id = $this->ext_settings->pixel_id;

        if (empty($access_token) || empty($pixel_id) || empty($item->product_id)) {
            return;
        }

        // Выбрать вариант, посчитать общую сумму
        $product = Product::getOne($item->product_id);
        $value = $product->price * intval($item->amount);
        $sku = $product->sku ?: $product->id;

        // Initialize
        Api::init(null, null, $access_token);
        $api = Api::instance();
        $api->setLogger(new CurlLogger());
        $events = array();


        // User Data
        $user_data = (new UserData())
            ->setClientIpAddress($_SERVER['REMOTE_ADDR']) # required
            ->setClientUserAgent($_SERVER['HTTP_USER_AGENT']);

        if (User::isLoggedIn()) {
            if (!empty(User::authUser('email'))) {
                $user_data->setEmails([hash('sha256', strtolower(User::authUser('email')))]);
            }
            if (!empty(User::authUser('phone'))) {
                $user_data->setPhones([hash('sha256', strtolower(User::authUser('phone')))]);
            }
            if (!empty(User::authUser('name'))) {
                $user_data->setFirstNames([hash('sha256', strtolower(User::authUser('name')))]);
            }
        }

        if ($fbc_cookie = Request::getCookie('_fbc', false)) {
            $user_data->setFbc($fbc_cookie);
        }

        if ($fbp_cookie = Request::getCookie('_fbp', false)) {
            $user_data->setFbp($fbp_cookie);
        }


        // Product Data
        $custom_data = (new CustomData())
            ->setValue($value)
            ->setCurrency(FinanceCurrency::getMainCurrency()->code)
            ->setContentIds(array($sku))
            ->setContentType("product");

        $event = (new Event())
            ->setEventName("AddToCart")
            ->setEventTime(time())
            ->setUserData($user_data)
            ->setCustomData($custom_data)
            ->setActionSource("website");

        if (!empty(Request::getSession('current_page'))) {
            $event->setEventSourceUrl(Config::get('root_url') .  Request::getSession('current_page'));
        }

        array_push($events, $event);

        $request = (new EventRequest($pixel_id))
            ->setEvents($events);

        $request->execute();
    }


    /**
     * Order Add
     * @param OrderAddEvent $event
     */
    #[AsEventListener]
    public function onOrderAddEvent(OrderAddEvent $event): void
    {
        if (empty($this->ext_settings->enabled)) {
            return;
        }

        $order = $event->getOrder();
        $purchases = OrderPurchase::getPurchases(['order_id' => $order->id], ['product']);

        // Should fill in value before running this script
        $access_token = $this->ext_settings->api_token;
        $pixel_id = $this->ext_settings->pixel_id;


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


    /**
     * Reques
     * @link https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/fbp-and-fbc
     * @param DesignBeforeFetchEvent $event
     */
    #[AsEventListener]
    public function onDesignBeforeFetchEvent(DesignBeforeFetchEvent $event): void
    {
        if (empty($this->ext_settings->enabled)) {
            return;
        }

        if (0) {
            $fbclid_get = Request::get('fbclid');

            // Example: fb.2.1736634312388.fbclid
            if ($fbc_cookie = Request::getCookie('_fbc', false)) {
                $fbc_cookie_arr = explode('.', $fbc_cookie);
                $fbclid_cookie = empty($fbc_cookie_arr[3]) ? null : $fbc_cookie_arr[3];
            }

            // Set FB Cookie
            if (!empty($fbclid_get) and (empty($fbclid_cookie) || $fbclid_cookie == 'fbclid' || $fbclid_get != $fbclid_cookie)) {
                $cookie_val = 'fb.2.' . time() . '.' . $fbclid_get;
                Request::setCookie('_fbc', $cookie_val, 90, '/', false);
            }
        }
    }
}
