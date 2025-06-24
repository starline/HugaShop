<?php

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
    public function updateOne($id, $entity)
    {
        RedirectUrlModel::updateOne($id, $entity);
    }

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
        }

        $links = RedirectUrlModel::getList();
        Design::assign('links', $links);

        return $this->getTemplatePath('templates/link_list.tpl');
    }

    public function link(?int $id = null)
    {
        if (!empty($link = Request::getDataAcces(RedirectUrlModel::getFields()))) {
            if (empty($link->id)) {
                $link = Design::setFlashMessage('add', RedirectUrlModel::create($link));
            } else {
                Design::setFlashMessage('update', RedirectUrlModel::updateOne($link->id, $link));
            }
            Request::makeRedirect("/admin/extension/RedirectUrl/link/$link->id");
        }

        if (!empty($id)) {
            $link = RedirectUrlModel::getOne($id);
            if (empty($link->id)) {
                Request::makeRedirect('/admin/extension/RedirectUrl');
            }
        }

        Design::assign('link', $link);
        return $this->getTemplatePath('templates/link.tpl');
    }

    #[AsEventListener(priority: 128)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || Request::isAjax() || Design::getTheme() === 'admin') {
            return;
        }

        $uri = urldecode($event->getRequest()->getPathInfo());

        $cache = Helper::cache(self::class);
        $cache_item = $cache->getItem('redirect_list');
        if (!$cache_item->isHit()) {
            $cache_item->set(RedirectUrlModel::getList(['enabled' => 1]));
            $cache->save($cache_item);
        }
        $links = $cache_item->get();

        foreach ($links as $link) {
            $pattern = '#^' . $link->url . '$#u';
            if (preg_match($pattern, $uri, $m)) {
                $redirect = $link->redirect;
                foreach ($m as $i => $v) {
                    if ($i === 0) continue;
                    $redirect = str_replace("[$i]", $v, $redirect);
                }
                RedirectUrlModel::updateOne($link->id, ['transitions' => $link->transitions + 1]);
                Request::makeRedirect($redirect, '301');
            }
        }
    }
}
