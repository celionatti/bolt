<?php

function generateLink($attributes)
{
    $link = '<a';

    foreach ($attributes as $key => $value) {
        $link .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
    }

    $link .= '>';

    return $link;
}


/**
 * Usage.
 */

// $linkAttributes = array(
//     'href' => '/home',
//     'class' => 'mb-2',
//     'target' => '_blank',
//     'title' => 'Go to Home Page',
//     'data-custom' => 'custom-data-value',
// );

// $link = generateLink($linkAttributes);
// echo $link . 'Home</a>';
