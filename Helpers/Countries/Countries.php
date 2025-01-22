<?php

declare(strict_types=1);

/**
 * =========================================
 * Bolt - Countries ========================
 * =========================================
 */

namespace celionatti\Bolt\Helpers\Countries;



class Countries
{
    private static $countries = [
        // Africa
        'DZ' => ['name' => 'Algeria', 'continent' => 'Africa'],
        'AO' => ['name' => 'Angola', 'continent' => 'Africa'],
        'BJ' => ['name' => 'Benin', 'continent' => 'Africa'],
        'BW' => ['name' => 'Botswana', 'continent' => 'Africa'],
        'BF' => ['name' => 'Burkina Faso', 'continent' => 'Africa'],
        'BI' => ['name' => 'Burundi', 'continent' => 'Africa'],
        'CM' => ['name' => 'Cameroon', 'continent' => 'Africa'],
        'CV' => ['name' => 'Cape Verde', 'continent' => 'Africa'],
        'TD' => ['name' => 'Chad', 'continent' => 'Africa'],
        'KM' => ['name' => 'Comoros', 'continent' => 'Africa'],
        'CI' => ['name' => 'Ivory Coast', 'continent' => 'Africa'],
        'DJ' => ['name' => 'Djibouti', 'continent' => 'Africa'],
        'EG' => ['name' => 'Egypt', 'continent' => 'Africa'],
        'GQ' => ['name' => 'Equatorial Guinea', 'continent' => 'Africa'],
        'ER' => ['name' => 'Eritrea', 'continent' => 'Africa'],
        'SZ' => ['name' => 'Eswatini', 'continent' => 'Africa'],
        'ET' => ['name' => 'Ethiopia', 'continent' => 'Africa'],
        'GA' => ['name' => 'Gabon', 'continent' => 'Africa'],
        'GM' => ['name' => 'Gambia', 'continent' => 'Africa'],
        'GH' => ['name' => 'Ghana', 'continent' => 'Africa'],
        'GN' => ['name' => 'Guinea', 'continent' => 'Africa'],
        'GW' => ['name' => 'Guinea-Bissau', 'continent' => 'Africa'],
        'KE' => ['name' => 'Kenya', 'continent' => 'Africa'],
        'LS' => ['name' => 'Lesotho', 'continent' => 'Africa'],
        'LR' => ['name' => 'Liberia', 'continent' => 'Africa'],
        'LY' => ['name' => 'Libya', 'continent' => 'Africa'],
        'MG' => ['name' => 'Madagascar', 'continent' => 'Africa'],
        'MW' => ['name' => 'Malawi', 'continent' => 'Africa'],
        'ML' => ['name' => 'Mali', 'continent' => 'Africa'],
        'MR' => ['name' => 'Mauritania', 'continent' => 'Africa'],
        'MU' => ['name' => 'Mauritius', 'continent' => 'Africa'],
        'MA' => ['name' => 'Morocco', 'continent' => 'Africa'],
        'MZ' => ['name' => 'Mozambique', 'continent' => 'Africa'],
        'NA' => ['name' => 'Namibia', 'continent' => 'Africa'],
        'NE' => ['name' => 'Niger', 'continent' => 'Africa'],
        'NG' => ['name' => 'Nigeria', 'continent' => 'Africa'],
        'RW' => ['name' => 'Rwanda', 'continent' => 'Africa'],
        'ST' => ['name' => 'São Tomé and Príncipe', 'continent' => 'Africa'],
        'SN' => ['name' => 'Senegal', 'continent' => 'Africa'],
        'SC' => ['name' => 'Seychelles', 'continent' => 'Africa'],
        'SL' => ['name' => 'Sierra Leone', 'continent' => 'Africa'],
        'SO' => ['name' => 'Somalia', 'continent' => 'Africa'],
        'ZA' => ['name' => 'South Africa', 'continent' => 'Africa'],
        'SS' => ['name' => 'South Sudan', 'continent' => 'Africa'],
        'SD' => ['name' => 'Sudan', 'continent' => 'Africa'],
        'TZ' => ['name' => 'Tanzania', 'continent' => 'Africa'],
        'TG' => ['name' => 'Togo', 'continent' => 'Africa'],
        'TN' => ['name' => 'Tunisia', 'continent' => 'Africa'],
        'UG' => ['name' => 'Uganda', 'continent' => 'Africa'],
        'ZM' => ['name' => 'Zambia', 'continent' => 'Africa'],
        'ZW' => ['name' => 'Zimbabwe', 'continent' => 'Africa'],

        // Asia
        'AF' => ['name' => 'Afghanistan', 'continent' => 'Asia'],
        'AM' => ['name' => 'Armenia', 'continent' => 'Asia'],
        'AZ' => ['name' => 'Azerbaijan', 'continent' => 'Asia'],
        'BH' => ['name' => 'Bahrain', 'continent' => 'Asia'],
        'BD' => ['name' => 'Bangladesh', 'continent' => 'Asia'],
        'BT' => ['name' => 'Bhutan', 'continent' => 'Asia'],
        'BN' => ['name' => 'Brunei', 'continent' => 'Asia'],
        'KH' => ['name' => 'Cambodia', 'continent' => 'Asia'],
        'CN' => ['name' => 'China', 'continent' => 'Asia'],
        'CY' => ['name' => 'Cyprus', 'continent' => 'Asia'],
        'IN' => ['name' => 'India', 'continent' => 'Asia'],
        'ID' => ['name' => 'Indonesia', 'continent' => 'Asia'],
        'IR' => ['name' => 'Iran', 'continent' => 'Asia'],
        'IQ' => ['name' => 'Iraq', 'continent' => 'Asia'],
        'IL' => ['name' => 'Israel', 'continent' => 'Asia'],
        'JP' => ['name' => 'Japan', 'continent' => 'Asia'],
        'JO' => ['name' => 'Jordan', 'continent' => 'Asia'],
        'KZ' => ['name' => 'Kazakhstan', 'continent' => 'Asia'],
        'KW' => ['name' => 'Kuwait', 'continent' => 'Asia'],
        'KG' => ['name' => 'Kyrgyzstan', 'continent' => 'Asia'],
        'LA' => ['name' => 'Laos', 'continent' => 'Asia'],
        'LB' => ['name' => 'Lebanon', 'continent' => 'Asia'],
        'MY' => ['name' => 'Malaysia', 'continent' => 'Asia'],
        'MV' => ['name' => 'Maldives', 'continent' => 'Asia'],
        'MN' => ['name' => 'Mongolia', 'continent' => 'Asia'],
        'MM' => ['name' => 'Myanmar', 'continent' => 'Asia'],
        'NP' => ['name' => 'Nepal', 'continent' => 'Asia'],
        'OM' => ['name' => 'Oman', 'continent' => 'Asia'],
        'PK' => ['name' => 'Pakistan', 'continent' => 'Asia'],
        'PH' => ['name' => 'Philippines', 'continent' => 'Asia'],
        'QA' => ['name' => 'Qatar', 'continent' => 'Asia'],
        'SA' => ['name' => 'Saudi Arabia', 'continent' => 'Asia'],
        'SG' => ['name' => 'Singapore', 'continent' => 'Asia'],
        'KR' => ['name' => 'South Korea', 'continent' => 'Asia'],
        'LK' => ['name' => 'Sri Lanka', 'continent' => 'Asia'],
        'SY' => ['name' => 'Syria', 'continent' => 'Asia'],
        'TJ' => ['name' => 'Tajikistan', 'continent' => 'Asia'],
        'TH' => ['name' => 'Thailand', 'continent' => 'Asia'],
        'TR' => ['name' => 'Turkey', 'continent' => 'Asia'],
        'TM' => ['name' => 'Turkmenistan', 'continent' => 'Asia'],
        'AE' => ['name' => 'United Arab Emirates', 'continent' => 'Asia'],
        'UZ' => ['name' => 'Uzbekistan', 'continent' => 'Asia'],
        'VN' => ['name' => 'Vietnam', 'continent' => 'Asia'],
        'YE' => ['name' => 'Yemen', 'continent' => 'Asia'],

        // Europe
        'AL' => ['name' => 'Albania', 'continent' => 'Europe'],
        'AD' => ['name' => 'Andorra', 'continent' => 'Europe'],
        'AT' => ['name' => 'Austria', 'continent' => 'Europe'],
        'BY' => ['name' => 'Belarus', 'continent' => 'Europe'],
        'BE' => ['name' => 'Belgium', 'continent' => 'Europe'],
        'BA' => ['name' => 'Bosnia and Herzegovina', 'continent' => 'Europe'],
        'BG' => ['name' => 'Bulgaria', 'continent' => 'Europe'],
        'HR' => ['name' => 'Croatia', 'continent' => 'Europe'],
        'CY' => ['name' => 'Cyprus', 'continent' => 'Europe'],
        'CZ' => ['name' => 'Czech Republic', 'continent' => 'Europe'],
        'DK' => ['name' => 'Denmark', 'continent' => 'Europe'],

        // North America
        'AG' => ['name' => 'Antigua and Barbuda', 'continent' => 'North America'],
        'BS' => ['name' => 'Bahamas', 'continent' => 'North America'],
        'BB' => ['name' => 'Barbados', 'continent' => 'North America'],
        'BZ' => ['name' => 'Belize', 'continent' => 'North America'],
        'CA' => ['name' => 'Canada', 'continent' => 'North America'],
        'CR' => ['name' => 'Costa Rica', 'continent' => 'North America'],
        'CU' => ['name' => 'Cuba', 'continent' => 'North America'],
        'DM' => ['name' => 'Dominica', 'continent' => 'North America'],
        'DO' => ['name' => 'Dominican Republic', 'continent' => 'North America'],
        'SV' => ['name' => 'El Salvador', 'continent' => 'North America'],
        'GD' => ['name' => 'Grenada', 'continent' => 'North America'],
        'GT' => ['name' => 'Guatemala', 'continent' => 'North America'],
        'HT' => ['name' => 'Haiti', 'continent' => 'North America'],
        'HN' => ['name' => 'Honduras', 'continent' => 'North America'],
        'JM' => ['name' => 'Jamaica', 'continent' => 'North America'],
        'MX' => ['name' => 'Mexico', 'continent' => 'North America'],
        'NI' => ['name' => 'Nicaragua', 'continent' => 'North America'],
        'PA' => ['name' => 'Panama', 'continent' => 'North America'],
        'KN' => ['name' => 'Saint Kitts and Nevis', 'continent' => 'North America'],
        'LC' => ['name' => 'Saint Lucia', 'continent' => 'North America'],
        'VC' => ['name' => 'Saint Vincent and the Grenadines', 'continent' => 'North America'],
        'TT' => ['name' => 'Trinidad and Tobago', 'continent' => 'North America'],
        'US' => ['name' => 'United States', 'continent' => 'North America'],

        // South America
        'AR' => ['name' => 'Argentina', 'continent' => 'South America'],
        'BO' => ['name' => 'Bolivia', 'continent' => 'South America'],
        'BR' => ['name' => 'Brazil', 'continent' => 'South America'],
        'CL' => ['name' => 'Chile', 'continent' => 'South America'],
        'CO' => ['name' => 'Colombia', 'continent' => 'South America'],
        'EC' => ['name' => 'Ecuador', 'continent' => 'South America'],
        'GY' => ['name' => 'Guyana', 'continent' => 'South America'],
        'PY' => ['name' => 'Paraguay', 'continent' => 'South America'],
        'PE' => ['name' => 'Peru', 'continent' => 'South America'],
        'SR' => ['name' => 'Suriname', 'continent' => 'South America'],
        'UY' => ['name' => 'Uruguay', 'continent' => 'South America'],
        'VE' => ['name' => 'Venezuela', 'continent' => 'South America'],
    ];

    /**
     * Get countries with optional filtering and output format.
     *
     * @param array $include Only include these countries by code.
     * @param array $exclude Exclude these countries by code.
     * @param string $filterBy Filter countries by a specific key.
     * @param bool $simplified Return only code and name if true.
     * @return array Filtered and formatted countries.
     */
    public static function getCountries(array $include = [], array $exclude = [], string $filterBy = 'continent', bool $simplified = false): array
    {
        $countries = self::$countries;

        // Include specific countries if $include is not empty
        if (!empty($include)) {
            $countries = array_filter($countries, fn($code) => in_array($code, $include, true), ARRAY_FILTER_USE_KEY);
        }

        // Exclude specific countries if $exclude is not empty
        if (!empty($exclude)) {
            $countries = array_filter($countries, fn($code) => !in_array($code, $exclude, true), ARRAY_FILTER_USE_KEY);
        }

        // Filter by a specific key if $filterBy exists
        if ($filterBy) {
            $countries = array_filter($countries, fn($data) => isset($data[$filterBy]));
        }

        // Return simplified format if $simplified is true
        if ($simplified) {
            return array_map(
                fn($code, $data) => ['code' => $code, 'name' => $data['name']],
                array_keys($countries),
                $countries
            );
        }

        return $countries;
    }

    /**
     * Get all countries.
     */
    public static function getAll(): array
    {
        return self::$countries;
    }

    /**
     * Get countries by continent.
     */
    public static function getByContinent(string $continent): array
    {
        return array_filter(self::$countries, fn($country) => $country['continent'] === $continent);
    }

    /**
     * Get a single country by code.
     */
    public static function getCountryByCode(string $code): ?array
    {
        return self::$countries[$code] ?? null;
    }

    /**
     * Check if a country code exists.
     */
    public static function exists(string $code): bool
    {
        return isset(self::$countries[$code]);
    }

    /**
     * Add a country.
     */
    public static function addCountry(string $code, string $name, string $continent): void
    {
        self::$countries[$code] = ['name' => $name, 'continent' => $continent];
    }

    /**
     * Exclude countries by a list of codes.
     */
    public static function excludeCountries(array $codes): void
    {
        self::$countries = array_filter(self::$countries, fn($code) => !in_array($code, $codes), ARRAY_FILTER_USE_KEY);
    }

    /**
     * Exclude countries by continent.
     */
    public static function excludeByContinent(string $continent): void
    {
        self::$countries = array_filter(self::$countries, fn($country) => $country['continent'] !== $continent);
    }
}