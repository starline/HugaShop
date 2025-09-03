<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Addons\ProductConfigurator\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\ProductConfigurator\Models\ConfiguratorStep;
use HugaShop\Addons\ProductConfigurator\Models\ConfiguratorOption;

final class StepController extends BaseAdminController
{
    use BaseAddonTrait;

    #[Route('/ProductConfigurator/step/{configurator_id}', name: 'AddonProductConfiguratorStepNew', priority: 20)]
    #[Route('/ProductConfigurator/step/{configurator_id}/{id}', name: 'AddonProductConfiguratorStep', priority: 20)]
    public function step(int $configurator_id, ?int $id = null)
    {
        $step = new \stdClass();
        $step->configurator_id = $configurator_id;

        if (Secure::checkCSRF()) {
            if (!empty($data = Secure::getInputCheckEditAccess(ConfiguratorStep::class, $id))) {
                $data->configurator_id = $configurator_id;
                if (empty($data->id)) {
                    $data = Design::setFlashMessage('add', ConfiguratorStep::createOne($data));
                } else {
                    $data = Design::setFlashMessage('update', ConfiguratorStep::updateOne($data->id, $data));
                }
                $option_ids    = Request::post('option_id', []);
                $option_names  = Request::post('option_name', []);
                $option_prices = Request::post('option_price', []);
                $keep_ids = [];
                foreach ($option_names as $i => $name) {
                    if (trim($name) === '') {
                        continue;
                    }
                    $price = floatval($option_prices[$i] ?? 0);
                    $opt_id = intval($option_ids[$i] ?? 0);
                    $option = ConfiguratorOption::updateOrCreate(
                        ['id' => $opt_id],
                        [
                            'step_id'  => $data->id,
                            'name'     => $name,
                            'price'    => $price,
                            'position' => $i,
                        ]
                    );
                    $keep_ids[] = $option->id;
                }
                ConfiguratorOption::where('step_id', $data->id)->whereNotIn('id', $keep_ids)->delete();
                return $this->redirectToRoute('AddonProductConfiguratorStep', ['configurator_id' => $configurator_id, 'id' => $data->id]);
            }
        }

        if (!empty($id)) {
            $step = ConfiguratorStep::getOne($id);
            if (empty($step->id) || $step->configurator_id != $configurator_id) {
                return $this->redirectToRoute('AddonProductConfigurator', ['id' => $configurator_id]);
            }
        }

        $options = ConfiguratorOption::getList(['step_id' => $step->id ?? 0], order: 'position');

        Design::assign('step', $step);
        Design::assign('options', $options);
        Design::assign('configurator_id', $configurator_id);

        return $this->fetchAddonResponse('step.tpl');
    }
}
