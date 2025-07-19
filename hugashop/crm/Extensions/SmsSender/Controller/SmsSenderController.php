<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
 *
 */

namespace HugaShop\Extensions\SmsSender\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use HugaShop\Models\Settings;
use HugaShop\Models\Order\Order;
use HugaShop\Models\Product\Product;
use HugaShop\Models\User\UserMailing;
use HugaShop\Models\User\UserNotifier;
use HugaShop\Extensions\BaseExtensionTrait;
use App\Controller\BaseAdminController;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Models\User\UserMailTemplate;
use HugaShop\Extensions\SmsSender\Models\SmsSenderMail;
use HugaShop\Extensions\SmsSender\Models\SmsSender;

final class SmsSenderController extends BaseAdminController
{
    use BaseExtensionTrait;

    /**
     * Рассылка
     */
    #[Route('/SmsSender/mailing', name: 'ExtSmsSenderMailingNew', priority: 20)]
    #[Route('/SmsSender/mailing/{id}', name: 'ExtSmsSenderMailing', priority: 20)]
    public function mailing(?int $id = null)
    {
        $mailing_list = [];

        // Update
        if (!empty($mailing = Request::getDataAcces(SmsSender::getFields()))) {
            $product_list = preg_split('/\r\n|\r|\n/', Request::post('product_list', 'string'));
            $mailing->product_list = serialize($product_list);

            $category_list = preg_split('/\r\n|\r|\n/', Request::post('category_list', 'string'));
            $mailing->category_list = serialize($category_list);

            $category_product_ids = Product::getProducts(['category_id' => $category_list])->pluck('id')->all();

            $user_prod_list = [];
            $product_list = array_merge($product_list, $category_product_ids);

            if (!empty($product_list)) {
                foreach ($product_list as $product_id) {
                    if (empty($product_id)) {
                        continue;
                    }
                    $orders = Order::getOrders(['product_id' => $product_id]);
                    foreach ($orders as $order) {
                        $user_prod_list[] = $order->phone;
                    }
                }
            }

            $user_old_list = preg_split('/\r\n|\r|\n/', Request::post('user_list'));
            $user_list = array_merge($user_prod_list, $user_old_list);
            $user_list = array_unique($user_list, SORT_STRING);
            $mailing->user_list = serialize($user_list);

            if (empty($mailing->id)) {
                $mailing->token = Helper::makeToken(uniqid(), 5);
                $mailing = Design::setFlashMessage('add', SmsSender::createOne($mailing));
            } else {
                Design::setFlashMessage('update', SmsSender::updateOne($mailing->id, $mailing));
            }

            return $this->redirectToRoute('ExtSmsSenderMailing', ['id' => $mailing->id]);
        }

        // View
        if (!empty($id)) {
            $mailing = SmsSender::getOne($id);
            if (empty($mailing->id)) {
                return $this->redirectToRoute('ExtSmsSenderList');
            }

            if (!empty($mailing->product_list)) {
                $mailing->product_list = unserialize($mailing->product_list);
            }
            if (!empty($mailing->category_list)) {
                $mailing->category_list = unserialize($mailing->category_list);
            }
            if (!empty($mailing->user_list)) {
                $mailing->user_list = unserialize($mailing->user_list);
            }

            if (!empty(Request::post('sending')) && !empty($mailing->notifier_id)) {
                foreach ($mailing->user_list as $phone) {
                    if (empty($phone)) {
                        continue;
                    }

                    $mail = new \stdClass();
                    $mail->notifier_id = $mailing->notifier_id;
                    $mail->contact = $phone;
                    $mail->template_id = $mailing->template_id;
                    $mail->settings = ['landing_url' => $mailing->landing_url];

                    $mail->id = UserMailing::addMailing($mail, false);
                    if (!empty($mail->id)) {
                        SmsSenderMail::createOne(['sender_id' => $mailing->id, 'mail_id' => $mail->id]);
                    }
                }
            }

            $filter = [];
            $filter['page'] = max(1, Request::getInt('page'));
            $filter['limit'] = Request::get('page', 'string') == 'all' ? 'all' : Settings::getParam('products_num_admin');

            $mailing_list   = SmsSenderMail::getMailingList($mailing->id, $filter);
            $mailing_count  = SmsSenderMail::getMailingCount($mailing->id, $filter);

            Design::assign('mailing_list', $mailing_list);
            Design::assign('mailing_count', $mailing_count);
            Design::assign('pages_count', ceil($mailing_count / Settings::getParam('products_num_admin')));
            Design::assign('current_page', $filter['page']);
        }

        if (!empty($mailing->template_id)) {
            $mailing->template = UserMailTemplate::getOne($mailing->template_id);
        }

        Design::assign('mailing', $mailing ?? null);
        Design::assign('notifiers', UserNotifier::getList(filter: ['enabled' => 1, 'type' => 'sms'], order: 'position'));
        Design::assign('extension', $this->getExtension());

        return $this->fetchExtResponse('mailing.tpl');
    }
}
