<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace HugaShop\Addons\CarouselPromo\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use App\Services\ImageService;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\CarouselPromo\Models\CarouselPromoBanner;
use HugaShop\Addons\CarouselPromo\CarouselPromo;

final class CarouselPromoController extends BaseAdminController
{

    use BaseAddonTrait;

    /**
     * Список страниц
     */
    #[Route('/CarouselPromo', name: 'AddonCarouselPromo', priority: 20)]
    public function template()
    {

        // Обработка действий
        if (Secure::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'disable':
                        CarouselPromoBanner::updateList($ids, ['enabled' => 0]);
                        break;
                    case 'enable':
                        CarouselPromoBanner::updateList($ids, ['enabled' => 1]);
                        break;
                    case 'delete':
                        foreach ($ids as $id) {
                            CarouselPromoBanner::deleteOne($id);
                        }
                        break;
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                CarouselPromoBanner::updateOne($id, ['position' => $position]);
            }
        }

        Design::assign('banners', CarouselPromoBanner::getList(order: 'position', join: ['image']));
        Design::assign('addon', $this->getAddon());

        return $this->fetchAddonResponse('index.tpl');
    }

    /**
     * Добавление/редактирование баннера
     */
    #[Route('/CarouselPromo/banner', name: 'AddonCarouselPromoBannerNew', priority: 20)]
    #[Route('/CarouselPromo/banner/{id}', name: 'AddonCarouselPromoBanner', priority: 20)]
    public function banner(?int $id = null)
    {
        #### Update
        ###########
        if (!empty($banner = Secure::getInputCheckEditAccess(CarouselPromoBanner::class, $id))) {
            if (empty($banner->id)) {
                $banner = Design::setFlashMessage('add', CarouselPromoBanner::createOne($banner));
            } else {
                Design::setFlashMessage('update', CarouselPromoBanner::updateOne($banner->id, $banner));
            }

            ImageService::catchImages($banner->id, CarouselPromo::class);

            return $this->redirectToRouteLang('AddonCarouselPromoBanner', ['id' => $banner->id]);
        }

        #### View
        #########
        if (!empty($id)) {
            $banner = CarouselPromoBanner::getOne($id, join: ['images']);
            if (empty($banner->id)) {
                return $this->redirectToRoute('AddonCarouselPromo');
            }
        }

        Design::assign('banner', $banner);
        Design::assign('addon', $this->getAddon());

        return $this->fetchAddonResponse('banner.tpl');
    }
}
