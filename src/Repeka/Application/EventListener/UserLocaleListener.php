<?php
namespace Repeka\Application\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class UserLocaleListener {

    /** @var string[] */
    private $fallbackLocales;

    public function __construct(array $fallbackLocales) {
        $this->fallbackLocales = $fallbackLocales;
    }

    public function onKernelRequest(GetResponseEvent $event) {
        $request = $event->getRequest();
        $cookielocale = $request->cookies->get('locale');
        if ($cookielocale !== null) {
            $request->setLocale($cookielocale);
        } else {
            $preferredLocale = $request->getPreferredLanguage($this->fallbackLocales);
            $request->setLocale($preferredLocale);
        }
    }
}
