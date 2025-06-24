<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
 *
 */

namespace HugaShop\Extensions\RedirectUrl;

use HugaShop\Models\Design;
use HugaShop\Models\Helper;
use HugaShop\Models\Request;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use HugaShop\Extensions\BaseExtension;
use HugaShop\Extensions\RedirectUrl\Models\RedirectUrl as RedirectUrlModel;

final class RedirectUrl extends BaseExtension
{

    /**
     * Url List
     */
    public function index()
    {
        if (Request::checkCSRF()) {
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'disable':
                        RedirectUrlModel::updateOne($ids, ['enabled' => 0]);
                        break;
                    case 'enable':
                        RedirectUrlModel::updateOne($ids, ['enabled' => 1]);
                        break;
                    case 'delete':
                        foreach ($ids as $id) {
                            RedirectUrlModel::deleteOne($id);
                        }
                        break;
                }
            }

            Helper::cache(self::class)->clear(); # Cache clean
        }

        $links = RedirectUrlModel::getList();
        Design::assign('links', $links);

        return $this->getTemplatePath('templates/link_list.tpl');
    }


    /**
     * link
     */
    public function link(?int $id = null)
    {

        #### Update
        ###########
        if (!empty($link = Request::getDataAcces(RedirectUrlModel::getFields()))) {
            if (empty($link->id)) {
                $link = Design::setFlashMessage('add', RedirectUrlModel::create($link));
            } else {
                Design::setFlashMessage('update', RedirectUrlModel::updateOne($link->id, $link));
            }

            Helper::cache(self::class)->clear(); # Cache clean
            Request::makeRedirect("/admin/extension/RedirectUrl/link/$link->id");
        }


        #### View
        #########
        if (!empty($id)) {
            $link = RedirectUrlModel::getOne($id);
            if (empty($link->id)) {
                Request::makeRedirect('/admin/extension/RedirectUrl');
            }
        }

        Design::assign('link', $link);
        return $this->getTemplatePath('templates/link.tpl');
    }


    /**
     * Kernel Event
     */
    #[AsEventListener(priority: 128)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || Request::isAjax()) {
            return;
        }

        $uri = urldecode($event->getRequest()->getPathInfo()); # Example: /test-link

        // Исключаем /admin и всё, что под ним
        if (str_starts_with($uri, '/admin')) {
            return;
        }

        $cache = Helper::cache(self::class);
        $cache_item = $cache->getItem('redirect_list');
        if (!$cache_item->isHit()) {
            $cache_item->set(RedirectUrlModel::getList(['enabled' => 1]));
            $cache->save($cache_item);
        }
        $links = $cache_item->get();

        foreach ($links as $link) {
            [$pattern, $names] = $this->preparePattern($link->url);
            if (preg_match($pattern, $uri, $m)) {
                $redirect = $link->redirect;
                foreach ($m as $i => $v) {
                    if ($i === 0) continue;
                    $redirect = str_replace("[$i]", $v, $redirect);
                    if (isset($names[$i - 1])) {
                        $redirect = str_replace('[' . $names[$i - 1] . ']', $v, $redirect);
                    }
                }

                RedirectUrlModel::where('id', $link->id)->increment('transitions');
                Request::makeRedirect($redirect, '301');
            }
        }
    }


    /**
     * Pattern 
     */
    private function preparePattern(string $mask): array
    {
        $names = [];
        $regex = '';
        $offset = 0;

        while (preg_match('/\{(\w+)(?::([^}]+))?\}/', $mask, $m, PREG_OFFSET_CAPTURE, $offset)) {
            $regex .= preg_quote(substr($mask, $offset, $m[0][1] - $offset), '#');
            $names[] = $m[1][0];
            $regex .= '(' . ($m[2][0] ?? '[^/]+') . ')';
            $offset = $m[0][1] + strlen($m[0][0]);
        }

        $regex .= preg_quote(substr($mask, $offset), '#');

        return ['#^' . $regex . '$#u', $names];
    }
}
