<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
 *
 */

namespace HugaShop\Extensions\InfoBlock;

use HugaShop\Models\Design;
use HugaShop\Models\Helper;
use HugaShop\Models\Request;
use HugaShop\Extensions\BaseExtension;
use HugaShop\Extensions\InfoBlock\Models\InfoBlock as InfoBlockModel;

final class InfoBlock extends BaseExtension
{


    /**
     * InfoBlock List
     */
    public function index()
    {

        // Обработка действий
        if (Request::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'disable': {
                            InfoBlockModel::updateOne($ids, ['enabled' => 0]);
                            break;
                        }
                    case 'enable': {
                            InfoBlockModel::updateOne($ids, ['enabled' => 1]);
                            break;
                        }
                    case 'delete': {
                            foreach ($ids as $id) {
                                InfoBlockModel::deleteOne($id);
                            }
                            break;
                        }
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                InfoBlockModel::updateOne($id, ['position' => $position]);
            }

            Helper::cache(self::class)->clear();
        }

        $blocks = InfoBlockModel::getList([], 'position');
        Design::assign('blocks', $blocks);

        return $this->getTemplatePath('templates/block_list.tpl');
    }


    /**
     * SEO block
     * @param int $block_id
     */
    public function block(?int $id = null)
    {

        #### Update
        ###########
        if (!empty($block = Request::getDataAcces(InfoBlockModel::getFields()))) {

            if (empty($block->id)) {
                $block = Design::setFlashMessage('add', InfoBlockModel::create($block));
            } else {
                Design::setFlashMessage('update', InfoBlockModel::updateOne($block->id, $block));
                Helper::cache(self::class)->clear();
            }

            Request::makeRedirect("/admin/extension/InfoBlock/block/$block->id");
        }


        #### View
        #########
        if (!empty($id)) {
            $block = InfoBlockModel::getOne($id);
            if (empty($block->id)) {
                Request::makeRedirect("/admin/extension/InfoBlock");
            }
        }

        Design::assign('block', $block);

        return $this->getTemplatePath('templates/block.tpl');
    }


    /**
     * Get block template
     * Use Cache
     */
    public function getTemplate(array $params = [])
    {

        if (empty($params['id'])) {
            return;
        }

        $enabled = $params['enabled'] ?? '1';

        $cache_item = Helper::cache(self::class)->getItem('item_' . $params['id']);
        if (!$cache_item->isHit()) {

            $block = InfoBlockModel::getOne(['id' => intval($params['id']), 'enabled' => $enabled]);
            if (empty($block->body)) {
                return;
            }

            Design::assign('InfoBlock', $block->body);
            $info_block = $this->fetchTemplate('templates/info_block.tpl');

            Helper::cache(self::class)->save($cache_item->set($info_block));
        } else {
            $info_block = $cache_item->get();
        }

        return $info_block;
    }
}
