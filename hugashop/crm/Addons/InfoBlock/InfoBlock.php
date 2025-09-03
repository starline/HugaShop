<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
 *
 */

namespace HugaShop\Addons\InfoBlock;

use HugaShop\Services\Cache;
use HugaShop\Services\Design;
use HugaShop\Models\Localization\Language;
use HugaShop\Addons\BaseAddon;
use HugaShop\Addons\InfoBlock\Models\InfoBlock as InfoBlockModel;

final class InfoBlock extends BaseAddon
{

    /**
     * Get block template
     * Use Cache
     */
    public static function getTemplate(array $params = [])
    {
        if (empty($params['id'])) {
            return;
        }

        $enabled = $params['enabled'] ?? '1';
        $lang = Language::getCurrent()->code;

        $cache_item = Cache::cache(InfoBlockModel::class)->getItem('item_' . $params['id'] . '_' . $lang);
        if (!$cache_item->isHit()) {

            $block = InfoBlockModel::getOneTranslate(['id' => intval($params['id']), 'enabled' => $enabled]);
            if (empty($block->body)) {
                return;
            }

            Design::assign('InfoBlock', $block->body);
            $info_block = self::fetchTemplate('info_block.tpl');

            Cache::cache(InfoBlockModel::class)->save($cache_item->set($info_block));
        } else {
            $info_block = $cache_item->get();
        }

        return $info_block;
    }
}
