<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Addons\ProductConfigurator;

use HugaShop\Addons\BaseAddon;
use HugaShop\Addons\ProductConfigurator\Models\Configurator;
use HugaShop\Addons\ProductConfigurator\Models\ConfiguratorStep;
use HugaShop\Addons\ProductConfigurator\Models\ConfiguratorOption;

final class ProductConfigurator extends BaseAddon
{
    /**
     * Получаем конфигуратор с шагами и опциями
     */
    public static function get_configurator(int $id)
    {
        $configurator = Configurator::getOne(['id' => $id, 'enabled' => 1]);
        if (empty($configurator->id)) {
            return null;
        }
        $configurator->steps = ConfiguratorStep::getList(
            ['configurator_id' => $configurator->id],
            order: ['position', 'asc']
        );
        foreach ($configurator->steps as $step) {
            $step->options = ConfiguratorOption::getList(
                ['step_id' => $step->id],
                order: ['position', 'asc']
            );
        }
        return $configurator;
    }
}
