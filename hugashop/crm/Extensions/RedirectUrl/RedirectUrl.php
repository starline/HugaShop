<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.7
 *
 */

namespace HugaShop\Extensions\RedirectUrl;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
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

            RedirectUrlModel::cacheClear(); # Cache clean
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
                $link = Design::setFlashMessage('add', RedirectUrlModel::createOne($link));
            } else {
                Design::setFlashMessage('update', RedirectUrlModel::updateOne($link->id, $link));
            }

            RedirectUrlModel::cacheClear(); # Cache clean
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
}
