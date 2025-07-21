<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
 *
 */

namespace App\Controller;

use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Models\User\User;
use HugaShop\Services\Request;
use App\Event\DesignBeforeFetchEvent;
use Symfony\Component\Asset\Packages;
use HugaShop\Models\Localization\Language;
use Symfony\Component\Translation\Translator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bridge\Twig\Extension\ImportMapRuntime;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends AbstractController
{

    public $route;

    public function __construct(
        public LocaleSwitcher $LocaleSwitcher,
        public Packages $Packages,
        public RequestStack $requestStack,
        public UrlGeneratorInterface $UrlGenerator,
        public ?Profiler $profiler
    ) {

        // Inject Symfony session
        Request::startSession($this->requestStack->getSession());

        Design::setModifierPlugin('linkLang', $this, 'generateUrlWithLocale');
        Design::setModifierPlugin('link', $UrlGenerator, 'generate');
        
        Design::assign('route', $this->route = $this->requestStack->getCurrentRequest()->attributes->get('_route'));

        // Show Profiler
        if (!is_null($profiler) and !User::authUser('manager')) {
            $profiler->disable();
        }
    }


    /**
     * Fetch ALL Content
     * @param string $content_tpl
     * @param ?string $block
     * @param ?string $template_dir
     */
    public function fetch(string $content_tpl, ?string $block = null, ?string $template_dir = null)
    {

        // Point for BeforeFetchEvent
        $this->setEvent(new DesignBeforeFetchEvent($content_tpl));
        Design::setFunctionPlugin('importmap', $this, 'getImportmap');

        // Ajax
        if (Request::isAjax()) {
            $block = $block ?? 'content';
            Design::assign('ajax_block', $block);
            $content = Design::fetch('extends:wrapper/ajax.tpl|' . $content_tpl, $template_dir);
        }

        // Normal Content
        else {
            $content = Design::fetch($content_tpl, $template_dir) . Helper::getCoreStats();
            Request::setCurrentPage();
        }

        return $content;
    }


    /**
     * Fetch Response
     * @param string $content_tpl
     */
    public function fetchResponse(string $content_tpl, ?string $block = null, ?string $template_dir = null)
    {
        // flash message
        foreach (['success', 'empty', 'error'] as $message_type) {
            if (!empty($service_message = Helper::getSessionMessage('message_' . $message_type))) {
                Design::append('service_messages_' . $message_type, $service_message);
            }
        }

        return new Response($this->fetch($content_tpl, $block, $template_dir));
    }


    /**
     * Set Translator
     * @param string $locale
     * @param string $theme
     */
    public function setTranslator(string $locale, string $theme)
    {
        $this->LocaleSwitcher->setLocale($locale);

        $Translator = new Translator($locale);

        // If Translation file exists
        $translate_file_path = Config::get('templates_dir') . $theme . '/translations/messages.' . $locale . '.yaml';
        if (file_exists($translate_file_path)) {
            $Translator->addLoader('yaml', new YamlFileLoader());
            $Translator->addResource('yaml', $translate_file_path, $locale);
        }

        Design::setTranslator($Translator);
        Design::setModifierPlugin('trans', $Translator, 'trans');
    }


    /**
     * Add Lazy Service to container
     */
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'event.dispatcher' => EventDispatcherInterface::class,
            'kernel' => KernelInterface::class
        ]);
    }


    /**
     * Set Event
     * @param $event
     */
    public function setEvent($event)
    {
        return $this->container->get('event.dispatcher')->dispatch($event);
    }


    /**
     * Get importmap html
     * @param array $params
     */
    public function getImportmap(array $params)
    {
        if (empty($params['point'])) {
            return;
        }
        return $this->container->get('twig')->getRuntime(ImportMapRuntime::class)->importmap($params['point']);
    }


    /**
     * Generate URL with locale prefix
     */
    public function generateUrlWithLocale(string $routeName, array $params = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        if ($routeName === 'current') {
            $request    = $this->requestStack->getCurrentRequest();
            $url        = $request->getPathInfo();
            $query      = $request->getQueryString();
            if ($query) {
                $url .= '?' . $query;
            }
        } else {
            $url = $this->UrlGenerator->generate($routeName, $params, $referenceType);
        }

        $languageCode = $params['locale'] ?? Language::checkOrGetCode();
        if ($languageCode && $languageCode !== Language::getMain()->code) {
            if ($url === '/' || $url === '') {
                $url = '/' . $languageCode;
            } else {
                $url = '/' . $languageCode . $url;
            }
        }

        return $url;
    }
}
