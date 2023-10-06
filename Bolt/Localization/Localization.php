<?php

declare(strict_types=1);

/**
 * =====================================
 * Bolt - Localization =================
 * =====================================
 */

namespace Bolt\Bolt\Localization;

class Localization
{
    private $locale;
    private $fallbackLocale;
    private $translations = [];

    public function __construct($defaultLocale, $fallbackLocale)
    {
        $this->locale = $defaultLocale;
        $this->fallbackLocale = $fallbackLocale;
    }

    public function setTranslation($locale, $key, $value)
    {
        // Set a translation for a specific locale.
        $this->translations[$locale][$key] = $value;
    }

    public function translate($key, $placeholders = [])
    {
        // Translate a key into the current locale.
        $translation = $this->translations[$this->locale][$key] ?? null;

        if ($translation === null && $this->locale !== $this->fallbackLocale) {
            // If the translation is missing, try the fallback locale.
            $translation = $this->translations[$this->fallbackLocale][$key] ?? null;
        }

        if ($translation === null) {
            // If still no translation found, return the key itself.
            return $key;
        }

        // Replace placeholders in the translation.
        foreach ($placeholders as $placeholder => $replacement) {
            $translation = str_replace(":{$placeholder}", $replacement, $translation);
        }

        return $translation;
    }

    public function setLocale($locale)
    {
        // Set the current locale to a new one.
        // Useful when the user wants to change the language.
        $this->locale = $locale;
    }

    public function getCurrentLocale()
    {
        // Get the currently selected locale.
        return $this->locale;
    }

    public function setFallbackLocale($fallbackLocale)
    {
        // Set a fallback locale in case a translation is missing in the current locale.
        // This helps ensure that there is always a valid translation.
        $this->fallbackLocale = $fallbackLocale;
    }

    public function getCurrentLocaleTranslations()
    {
        // Get translations for the currently selected locale.
        return $this->translations[$this->locale];
    }

    public function getFallbackLocaleTranslations()
    {
        // Get translations for the fallback locale.
        return $this->translations[$this->fallbackLocale];
    }

    public function switchToFallbackLocale()
    {
        // Switch to the fallback locale for the current session.
        // Useful when you want to provide content even if it's not fully translated.
        $this->locale = $this->fallbackLocale;
    }

    public function getAllSupportedLocales()
    {
        // Return an array of all supported locales.
        // This can be used to generate language selection options.
        return array_keys($this->translations);
    }

    public function formatCurrency($amount, $currencyCode)
    {
        // Example: Implement currency formatting logic for different locales.

        // Replace this with your actual currency formatting logic
        $formattedAmount = number_format($amount, 2); // Format to 2 decimal places

        // You may want to add currency symbols and handle different currency codes.
        switch ($currencyCode) {
            case 'USD':
                $formattedAmount = '$' . $formattedAmount;
                break;
            case 'EUR':
                $formattedAmount = '€' . $formattedAmount;
                break;
            case 'GBP':
                $formattedAmount = '£' . $formattedAmount;
                break;
            case 'JPY':
                $formattedAmount = '¥' . $formattedAmount;
                break;
            case 'NGN':
                $formattedAmount = '₦' . $formattedAmount;
                break;
                // Add more cases for other currency codes as needed.
        }

        return $formattedAmount;
    }


    public function formatDate($date, $format = 'short', $locale = 'en_US')
    {
        // Define date formats for different locales
        $dateFormats = [
            'en_US' => [
                'short' => 'm/d/y',         // Short date format (e.g., 01/15/22)
                'long' => 'F j, Y',         // Long date format (e.g., January 15, 2022)
            ],
            'fr_FR' => [
                'short' => 'd/m/y',         // Short date format for French (e.g., 15/01/22)
                'long' => 'j F Y',          // Long date format for French (e.g., 15 janvier 2022)
            ],
            'de_DE' => [
                'short' => 'd.m.y',         // Short date format for German (e.g., 15.01.22)
                'long' => 'j. F Y',         // Long date format for German (e.g., 15. Januar 2022)
            ],
            // Add more locale-specific formats as needed
        ];

        // Check if the provided locale is supported; default to 'en_US' if unsupported
        if (!isset($dateFormats[$locale])) {
            $locale = 'en_US';
        }

        // Check if the provided format is supported; default to 'short' if unsupported
        if (!isset($dateFormats[$locale][$format])) {
            $format = 'short';
        }

        // Format the date based on the selected locale and format
        return date($dateFormats[$locale][$format], strtotime($date));
    }


    public function formatNumber($number, $style = 'decimal', $locale = 'en_US')
    {
        // Define number formatting styles for different locales
        $numberStyles = [
            'en_US' => [
                'decimal' => ',',
                'thousands' => '.',
                'decimals' => 2, // Number of decimal places
            ],
            'fr_FR' => [
                'decimal' => ',',
                'thousands' => ' ',
                'decimals' => 2,
            ],
            'de_DE' => [
                'decimal' => ',',
                'thousands' => '.',
                'decimals' => 2,
            ],
            // Add more locale-specific number styles here
        ];

        // Check if the provided locale is supported; default to 'en_US' if unsupported
        if (!isset($numberStyles[$locale])) {
            $locale = 'en_US';
        }

        // Check if the provided style is supported; default to 'decimal' if unsupported
        if (!isset($numberStyles[$locale][$style])) {
            $style = 'decimal';
        }

        // Get the number formatting style for the selected locale and style
        $decimalSeparator = $numberStyles[$locale]['decimal'];
        $thousandsSeparator = $numberStyles[$locale]['thousands'];
        $decimals = $numberStyles[$locale]['decimals'];

        // Format the number using number_format function
        return number_format($number, $decimals, $decimalSeparator, $thousandsSeparator);
    }


    public function translateDate($date, $format = 'short', $locale = 'en_US')
    {
        // Define date translation for different locales
        $dateTranslations = [
            'en_US' => [
                'short' => [
                    'January' => 'Jan',
                    'February' => 'Feb',
                    'March' => 'Mar',
                    'April' => 'Apr',
                    'May' => 'May',
                    'June' => 'Jun',
                    'July' => 'Jul',
                    'August' => 'Aug',
                    'September' => 'Sep',
                    'October' => 'Oct',
                    'November' => 'Nov',
                    'December' => 'Dec',
                ],
                'long' => [
                    // Add long date translations here
                ],
            ],
            'fr_FR' => [
                'short' => [
                    'January' => 'janv.',
                    'February' => 'févr.',
                    'March' => 'mars',
                    'April' => 'avr.',
                    'May' => 'mai',
                    'June' => 'juin',
                    'July' => 'juil.',
                    'August' => 'août',
                    'September' => 'sept.',
                    'October' => 'oct.',
                    'November' => 'nov.',
                    'December' => 'déc.',
                ],
                'long' => [
                    // Add long date translations here
                ],
            ],
            // Add more locale-specific date translations here
        ];

        // Check if the provided locale is supported; default to 'en_US' if unsupported
        if (!isset($dateTranslations[$locale])) {
            $locale = 'en_US';
        }

        // Check if the provided format is supported; default to 'short' if unsupported
        if (!isset($dateTranslations[$locale][$format])) {
            $format = 'short';
        }

        // Replace month names with translated versions
        $translatedDate = strtr($date, $dateTranslations[$locale][$format]);

        return $translatedDate;
    }


    public function translateNumber($number, $locale = 'en_US')
    {
        // Define number translation rules for different locales
        $numberTranslations = [
            'en_US' => [
                // English-style formatting (e.g., 1,000.00)
                'thousands_separator' => ',',
                'decimal_separator' => '.',
            ],
            'fr_FR' => [
                // French-style formatting (e.g., 1 000,00)
                'thousands_separator' => ' ',
                'decimal_separator' => ',',
            ],
            // Add more locale-specific number translations here
        ];

        // Check if the provided locale is supported; default to 'en_US' if unsupported
        if (!isset($numberTranslations[$locale])) {
            $locale = 'en_US';
        }

        // Get the thousands separator and decimal separator for the locale
        $thousandsSeparator = $numberTranslations[$locale]['thousands_separator'];
        $decimalSeparator = $numberTranslations[$locale]['decimal_separator'];

        // Replace separators to match the locale's conventions
        $translatedNumber = str_replace(',', $thousandsSeparator, $number);
        $translatedNumber = str_replace('.', $decimalSeparator, $translatedNumber);

        return $translatedNumber;
    }
}
