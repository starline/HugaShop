<?php
/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 *
 * Custom 404 error handler
 */

namespace App\EventListener;

use App\Services\LocaleService;
use HugaShop\Models\Settings;
use HugaShop\Services\Config;
use HugaShop\Services\Design;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;

class ExceptionListener
{
    #[AsEventListener(event: KernelEvents::EXCEPTION)]
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof NotFoundHttpException) {
            return;
        }

        LocaleService::prepare($event->getRequest());

        $theme  = Settings::getParam('theme');
        Design::initSettings(['theme' => $theme]);

        $locale = LocaleService::detectCode();
        $translator = new Translator($locale);
        $file = Config::get('templates_dir') . $theme . '/translations/messages.' . $locale . '.yaml';
        if (is_file($file)) {
            $translator->addLoader('yaml', new YamlFileLoader());
            $translator->addResource('yaml', $file, $locale);
        }

        Design::setTranslator($translator);
        Design::setModifierPlugin('trans', $translator, 'trans');
        Design::assign([
            'meta_title'       => $translator->trans('Страница не найдена'),
            'meta_description' => $translator->trans('Страница не найдена'),
        ]);

        $content = Design::fetch('404.php');
        $event->setResponse(new Response($content, Response::HTTP_NOT_FOUND));
    }
}
