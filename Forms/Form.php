<?php

declare(strict_types=1);

/**
 * ==========================================
 * Bolt - Form ==============================
 * ==========================================
 */

namespace celionatti\Bolt\Forms;

use celionatti\Bolt\Helpers\Csrf;

class Form
{
    public static function openForm($action, $method = 'POST', $enctype = null, $attrs = []): string
    {
        $enctypeAttribute = $enctype ? ' enctype="' . $enctype . '"' : '';
        $html = "<form action='" . htmlspecialchars($action) . "' method='$method'$enctypeAttribute";
        $html .= self::processAttrs($attrs);
        $html .= '>';

        return $html;
    }

    public static function closeForm(): string
    {
        return '</form>';
    }

    public static function csrfField(): string
    {
        $csrf = new Csrf();
        $token = $csrf->getToken() ?? "";

        return "<input type='hidden' name='_csrf_token' value='{$token}'>";
    }

    public static function method($method): string
    {
        return "<input type='hidden' value='{$method}' name='_method' />";
    }

    public static function hidden($name, $value, $attrs = []): string
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

    public static function inputForm($label, $id, $value, $inputAttrs = [], $wrapperAttrs = [], $errors = []): string
    {
        $inputAttrs = self::appendErrors($id, $inputAttrs, $errors);
        $wrapperAttrs['class'] = 'form-group ' . ($wrapperAttrs['class'] ?? '');
        $wrapperStr = self::processAttrs($wrapperAttrs);
        $inputAttrs['class'] = 'form-control ' . ($inputAttrs['class'] ?? '');
        $inputStr = self::processAttrs($inputAttrs);
        $errorMsg = array_key_exists($id, $errors) ? $errors[$id] : '';
        $html = "<div {$wrapperStr}>";
        $html .= "<label for='{$id}' class='form-label'>{$label}</label>";
        $html .= "<input id='{$id}' name='{$id}' value='{$value}' {$inputStr} placeholder='{$label}' />";
        $html .= "<div class='message'>{$errorMsg}</div></div>";
        return $html;
    }

    public static function selectForm($label, $id, $value, $options, $inputAttrs = [], $wrapperAttrs = [], $errors = []): string
    {
        $inputAttrs = self::appendErrors($id, $inputAttrs, $errors);
        $wrapperAttrs['class'] = 'form-group ' . ($wrapperAttrs['class'] ?? '');
        $wrapperStr = self::processAttrs($wrapperAttrs);
        $inputAttrs['class'] = 'form-control ' . ($inputAttrs['class'] ?? '');
        $inputStr = self::processAttrs($inputAttrs);
        $errorMsg = array_key_exists($id, $errors) ? $errors[$id] : '';
        $html = "<div {$wrapperStr}>";
        $html .= "<label for='{$id}' class='form-label'>{$label}</label>";
        $html .= "<select id='{$id}' name='{$id}' class='form-select' {$inputStr}>";
        foreach ($options as $val => $display) {
            $selected = $val == $value ? ' selected ' : '';
            $html .= "<option value='{$val}'{$selected}>{$display}</option>";
        }
        $html .= "</select>";
        $html .= "<div class='message'>{$errorMsg}</div></div>";
        return $html;
    }

    public static function checkForm($label, $id, $checked = '', $inputAttrs = [], $wrapperAttrs = [], $errors = []): string
    {
        $inputAttrs = self::appendErrors($id, $inputAttrs, $errors);
        $wrapperAttrs['class'] = 'form-check ' . ($wrapperAttrs['class'] ?? '');
        $wrapperStr = self::processAttrs($wrapperAttrs);
        $inputStr = self::processAttrs($inputAttrs);
        $checkedStr = $checked == 'on' ? 'checked' : '';
        $errorMsg = array_key_exists($id, $errors) ? $errors[$id] : '';
        $html = "<div {$wrapperStr}>";
        $html .= "<input type=\"checkbox\" id=\"{$id}\" name=\"{$id}\" {$inputStr} {$checkedStr} class='form-check-input'>";
        $html .= "<label class='form-check-label' for=\"{$id}\">{$label}</label>";
        $html .= "<div class='message'>{$errorMsg}</div></div>";
        return $html;
    }

    public static function textareaForm($label, $id, $value, $inputAttrs = [], $wrapperAttrs = [], $errors = []): string
    {
        $inputAttrs = self::appendErrors($id, $inputAttrs, $errors);
        $wrapperAttrs['class'] = 'form-group ' . ($wrapperAttrs['class'] ?? '');
        $wrapperStr = self::processAttrs($wrapperAttrs);
        $inputAttrs['class'] = 'form-control ' . ($inputAttrs['class'] ?? '');
        $inputStr = self::processAttrs($inputAttrs);
        $errorMsg = array_key_exists($id, $errors) ? $errors[$id] : '';
        $html = "<div {$wrapperStr}>";
        $html .= "<label for='{$id}' class='form-label'>{$label}</label>";
        $html .= "<textarea id='{$id}' name='{$id}' {$inputStr} placeholder='{$label}'>{$value}</textarea>";
        $html .= "<div class='message'>{$errorMsg}</div></div>";
        return $html;
    }

    public static function fileForm($label, $id, $input = [], $wrapper = [], $errors = []): string
    {
        $inputAttrs = self::appendErrors($id, $input, $errors);
        $wrapperAttrs['class'] = 'form-group ' . ($wrapperAttrs['class'] ?? '');
        $wrapperStr = self::processAttrs($wrapperAttrs);
        $inputAttrs['class'] = 'form-control-file ' . ($inputAttrs['class'] ?? '');
        $inputStr = self::processAttrs($inputAttrs);
        $errorMsg = array_key_exists($id, $errors) ? $errors[$id] : '';
        $html = "<div {$wrapperStr}>";
        $html .= "<label for=\"{$id}\" class='form-label'>{$label}</label>";
        $html .= "<input type=\"file\" id=\"{$id}\" name=\"{$id}\" {$inputStr} class='form-control-file'/>";
        $html .= "<div class=\"message\">{$errorMsg}</div></div>";
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

    protected static function processAttrs(array $attrs): string
    {
        $html = "";
        foreach ($attrs as $key => $value) {
            $html .= " $key='$value'";
        }
        return $html;
    }

    protected static function appendErrors(string $key, array $inputAttrs, array $errors): array
    {
        if (array_key_exists($key, $errors)) {
            $inputAttrs['class'] = ($inputAttrs['class'] ?? '') . ' is-invalid';
        }
        return $inputAttrs;
    }

    protected static function getOneError(string $id, array $errors): string
    {
        $error = $errors[$id] ?? [];
        return $error[0] ?? '';
    }
}
