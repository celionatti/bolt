<?php

class Localization
{
    private $locale;
    private $translations = [];

    public function __construct($locale = 'en_US')
    {
        $this->locale = $locale;
        $this->loadTranslations();
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function translate($key, $placeholders = [], $count = 1)
    {
        $translationKey = $this->getPluralizationKey($key, $count);

        if (isset($this->translations[$this->locale][$translationKey])) {
            $translation = $this->translations[$this->locale][$translationKey];

            if (!empty($placeholders)) {
                foreach ($placeholders as $placeholder => $value) {
                    $translation = str_replace("{{$placeholder}}", $value, $translation);
                }
            }

            return $translation;
        }

        // If the translation is missing, you can return the key itself or an error message.
        return "Translation missing for key: $key";
    }

    private function loadTranslations()
    {
        // Load translations from external files or any other source.
        // Store translations in the $this->translations array.
        // Example:
        $enTranslations = include('translations/en_US.php');
        $frTranslations = include('translations/fr_FR.php');

        $this->translations['en_US'] = $enTranslations;
        $this->translations['fr_FR'] = $frTranslations;

        // You can have translations for more languages and pluralization rules.
    }

    private function getPluralizationKey($key, $count)
    {
        // Implement pluralization rules for different languages as needed.
        // This is a simplified example for English.
        return $count === 1 ? $key : $key . '_plural';
    }

    public static function detectLocale()
    {
        // Detect the user's preferred locale from the browser or session.
        // You can implement more advanced detection logic here.
        // Example:
        $userLocale = isset($_SESSION['user_locale']) ? $_SESSION['user_locale'] : 'en_US';

        return new self($userLocale);
    }
}

// Example usage:
$translator = Localization::detectLocale();

$name = 'John';

$welcomeMessage = $translator->translate('welcome', ['name' => $name], 1);
$pluralMessage = $translator->translate('apple', [], 5);

echo $welcomeMessage; // Outputs: Welcome, John!
echo $pluralMessage;  // Outputs: 5 apples
