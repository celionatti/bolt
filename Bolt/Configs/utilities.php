<?php

declare(strict_types=1);

/**
 * Bolt Utilities
 * 
 * Author: Celio Natti
 * Year: 2023
 */



/**
 * Render a bolt link, <a> tag HTML with optional additional attributes.
 *
 * @param string $href
 * @param string $class
 * @param string $content
 * @param string $target
 * @param array $attributes
 * @return string
 */
function bolt_link($href = '/', $class = '', $content, $attributes = [], $target = '_self'): string
{
    $html = '<a href="' . htmlspecialchars($href) . '" class="' . htmlspecialchars($class) . '" target="' . htmlspecialchars($target) . '"';

    // Add additional attributes if provided
    foreach ($attributes as $attr => $value) {
        $html .= ' ' . htmlspecialchars($attr) . '="' . htmlspecialchars($value) . '"';
    }

    $html .= '>' . htmlspecialchars($content) . '</a>';
    return $html;
}

/**
 * Render a dynamic dropdown menu with links for the menu items.
 *
 * @param string $linkText
 * @param array $menuItems
 * @param string $linkClass
 * @param string $menuClass
 * @param string $linkHref
 * @param array $linkAttributes
 * @param array $menuAttributes
 * @return string
 */
function bolt_dropdownLink(
    $linkText,
    $menuItems,
    $linkClass = 'nav-link dropdown-toggle',
    $menuClass = 'dropdown-menu',
    $linkHref = '#',
    $linkAttributes = [],
    $menuAttributes = []
): string {
    $linkAttributesString = '';
    foreach ($linkAttributes as $attr => $value) {
        $linkAttributesString .= ' ' . htmlspecialchars($attr) . '="' . htmlspecialchars($value) . '"';
    }

    $menuAttributesString = '';
    foreach ($menuAttributes as $attr => $value) {
        $menuAttributesString .= ' ' . htmlspecialchars($attr) . '="' . htmlspecialchars($value) . '"';
    }

    $html = '<a class="' . htmlspecialchars($linkClass) . '" href="' . htmlspecialchars($linkHref) . '"' . $linkAttributesString . ' aria-expanded="false">' . htmlspecialchars($linkText) . '</a>';
    $html .= '<ul class="' . htmlspecialchars($menuClass) . '"' . $menuAttributesString . '>';

    foreach ($menuItems as $item) {
        $html .= '<li><a class="dropdown-item" href="' . htmlspecialchars($item['href']) . '">' . htmlspecialchars($item['text']) . '</a></li>';
    }

    $html .= '</ul>';
    return $html;
}
