<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.7
 *
 */

namespace HugaShop\Addons\FacebookPixel\EventListener;

use FacebookAds\Api;
use App\Event\CartAddEvent;
use HugaShop\Services\Config;
use HugaShop\Models\User\User;
use HugaShop\Services\Request;
use FacebookAds\Logger\CurlLogger;
use HugaShop\Models\Product\Product;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\UserData;
use HugaShop\Addons\BaseAddonTrait;
use HugaShop\Models\Finance\FinanceCurrency;
use FacebookAds\Object\ServerSide\CustomData;
use FacebookAds\Object\ServerSide\EventRequest;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class CartAddListener
{

    use BaseAddonTrait;

    /**
     * Cart Add
     * @param CartAddEvent $event
     */
    #[AsEventListener]
    public function onCartAddEvent(CartAddEvent $event): void
    {

        $settings = $this->getSettings();
        if (empty($settings->enabled)) {
            return;
        }

        $item = $event->getItem();

        // Should fill in value before running this script
        $access_token = $this->getSettings()->api_token;
        $pixel_id = $this->getSettings()->pixel_id;

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
}