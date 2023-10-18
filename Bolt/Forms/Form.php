<?php

declare(strict_types=1);

/**
 * ==========================================
 * Bolt - Form ==============================
 * ==========================================
 */

namespace Bolt\Bolt\Forms;

use Bolt\Bolt\Helpers\Csrf;
use Bolt\Bolt\Model;

class Form
{
    public static function openForm($action, $method = 'POST', $enctype = null, $attrs = [])
    {
        $csrf = new Csrf();
        $enctypeAttribute = $enctype ? ' enctype="' . $enctype . '"' : '';
        $html = "<form action='" . htmlspecialchars($action) . "' method='$method' $enctypeAttribute";

        // Add any additional attributes
        $html .= self::processAttrs($attrs);
        $token = $csrf->getToken() ?? "";

        $csrfField = "<input type='hidden' name='_csrf_token' value='{$token}'>";
        return $html . $csrfField;
    }

    public static function closeForm()
    {
        return '</form>';
    }

    public static function method($method)
    {
        return "<input type='hidden' value='{$method}' name='_method' />";
    }

    public static function hidden($name, $value, $attrs = [])
    {
        $html = "<input type='hidden' value='{$value}' id='{$name}' name='{$name}'";

        // Add any additional attributes
        $html .= self::processAttrs($attrs);

        $html .= ' />';
        return $html;
    }

    public static function input($name, $label, $value = '', $attrs = [])
    {
        $html = "<label for='$name'>$label</label>";
        $html .= "<input type='text' id='$name' name='$name' value='$value'";

        // Add any additional attributes
        $html .= self::processAttrs($attrs);

        $html .= ' />';
        return $html;
    }

    public static function textarea($name, $label, $value = '', $attrs = [])
    {
        $html = "<label for='$name'>$label</label>";
        $html .= "<textarea id='$name' name='$name'";

        // Add any additional attributes
        $html .= self::processAttrs($attrs);

        $html .= ">$value</textarea>";
        return $html;
    }

    public static function select($name, $label, $options = [], $selected = '', $attrs = [])
    {
        $html = "<label for='$name'>$label</label>";
        $html .= "<select id='$name' name='$name'";

        // Add any additional attributes
        $html .= self::processAttrs($attrs);

        $html .= '>';

        foreach ($options as $value => $text) {
            $isSelected = ($value == $selected) ? 'selected' : '';
            $html .= "<option value='$value' $isSelected>$text</option>";
        }

        $html .= '</select>';
        return $html;
    }

    public static function checkbox($name, $label, $checked = false, $attrs = [])
    {
        $checkedAttr = $checked ? 'checked' : '';
        $html = "<input type='checkbox' id='$name' name='$name' value='1' $checkedAttr";

        // Add any additional attributes
        $html .= self::processAttrs($attrs);

        $html .= ">";
        $html .= "<label for='$name'>$label</label>";
        return $html;
    }

    public static function radio($name, $label, $value, $checked = false, $attrs = [])
    {
        $checkedAttr = $checked ? 'checked' : '';
        $html = "<input type='radio' id='$name' name='$name' value='$value' $checkedAttr";

        // Add any additional attributes
        $html .= self::processAttrs($attrs);

        $html .= ">";
        $html .= "<label for='$name'>$label</label>";
        return $html;
    }

    public static function submitBtn($text, $attrs = [])
    {
        $html = "<button type='submit'";

        // Add any additional attributes
        $html .= self::processAttrs($attrs);

        $html .= ">$text</button>";
        return $html;
    }

    protected static function processAttrs($attrs)
    {
        $html = "";
        foreach ($attrs as $key => $value) {
            $html .= " $key='$value'";
        }
        return $html;
    }

    protected static function getOneError($id, $errors)
    {
        $error = $errors[$id] ?? [];
        return $error[0] ?? '';
    }
}
