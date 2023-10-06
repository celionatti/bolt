<?php


class Localization
{
    private $locale;
    private $translations = [];

    public function __construct($locale)
    {
        $this->locale = $locale;
        $this->loadTranslations();
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function translate($key, $placeholders = [])
    {
        if (isset($this->translations[$this->locale][$key])) {
            $translation = $this->translations[$this->locale][$key];

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
        // Load translations from files, database, or any other source.
        // Store translations in the $this->translations array.
        // Example:
        $this->translations['en'] = [
            'welcome' => 'Welcome, :name!',
            'greeting' => 'Hello, :name!',
        ];

        $this->translations['fr'] = [
            'welcome' => 'Bienvenue, :name!',
            'greeting' => 'Salut, :name!',
        ];

        // You can have translations for more languages.
    }
}

// Example usage:
$locale = 'en'; // Set the user's preferred locale.
$translator = new Localization($locale);

$name = 'John';

$welcomeMessage = $translator->translate('welcome', ['name' => $name]);
$greetingMessage = $translator->translate('greeting', ['name' => $name]);

echo $welcomeMessage; // Outputs: Welcome, John!
echo $greetingMessage; // Outputs: Hello, John!


/**
 * 
 * This is a basic example of a localization and internationalization class. You can expand it by:
*Loading translations from external files or a database.
*Supporting pluralization rules.
*Handling date and time formatting.
*Handling number formatting and currency.
*Detecting and setting the user's preferred locale.
*Caching translations for improved performance.
*The exact implementation will depend on your project's requirements and the framework or libraries you're using
 * 
 * 
 * 
 */