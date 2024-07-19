<?php

declare(strict_types=1);

/**
 * =====================================
 * Bolt - Translation ==================
 * =====================================
 */

namespace celionatti\Bolt\Localization;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\ArrayLoader;

class Translation
{
    private $translator;

    public function __construct($locale = 'en')
    {
        $this->translator = new Translator($locale);
        $this->translator->addLoader('array', new ArrayLoader());
    }

    public function addTranslation($locale, $messages)
    {
        $this->translator->addResource('array', $messages, $locale);
    }

    public function translate($id, $parameters = [], $locale = null)
    {
        return $this->translator->trans($id, $parameters, null, $locale);
    }

    public function setLocale($locale)
    {
        $this->translator->setLocale($locale);
    }
}