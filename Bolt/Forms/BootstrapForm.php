<?php

declare(strict_types=1);

/**
 * ==========================================
 * Bolt - BootstrapForm =====================
 * ==========================================
 */

namespace Bolt\Bolt\Forms;


class BootstrapForm extends Form
{
    public static function submitButton($label = 'Submit', $class = '', $attrs = [])
    {
        $attrs['class'] = "btn {$class}";
        return parent::submitBtn($label, $attrs);
    }

    public static function inputField($label, $id, $value, $inputAttrs = [], $wrapperAttrs = [], $errors = []): string
    {
        $inputAttrs = self::appendErrors($id, $inputAttrs, $errors);
        $wrapperAttrs['class'] = 'form-group ' . ($wrapperAttrs['class'] ?? '');
        $wrapperStr = self::processAttrs($wrapperAttrs);
        $inputAttrs['class'] = 'form-control ' . ($inputAttrs['class'] ?? '');
        $inputStr = self::processAttrs($inputAttrs);
        $errorMsg = array_key_exists($id, $errors) ? $errors[$id] : '';
        $html = "<div {$wrapperStr}>";
        $html .= "<label for='{$id}'>{$label}</label>";
        $html .= "<input id='{$id}' name='{$id}' value='{$value}' {$inputStr} placeholder='{$label}' />";
        $html .= "<div class='invalid-feedback'>{$errorMsg}</div></div>";
        return $html;
    }

    public static function selectField($label, $id, $value, $options, $inputAttrs = [], $wrapperAttrs = [], $errors = []): string
    {
        $inputAttrs = self::appendErrors($id, $inputAttrs, $errors);
        $wrapperAttrs['class'] = 'form-group ' . ($wrapperAttrs['class'] ?? '');
        $wrapperStr = self::processAttrs($wrapperAttrs);
        $inputAttrs['class'] = 'form-control ' . ($inputAttrs['class'] ?? '');
        $inputStr = self::processAttrs($inputAttrs);
        $errorMsg = array_key_exists($id, $errors) ? $errors[$id] : '';
        $html = "<div {$wrapperStr}>";
        $html .= "<label for='{$id}'>{$label}</label>";
        $html .= "<select id='{$id}' name='{$id}' {$inputStr} class='custom-select'>";
        foreach ($options as $val => $display) {
            $selected = $val == $value ? ' selected ' : '';
            $html .= "<option value='{$val}'{$selected}>{$display}</option>";
        }
        $html .= "</select>";
        $html .= "<div class='invalid-feedback'>{$errorMsg}</div></div>";
        return $html;
    }

    public static function checkField($label, $id, $checked = '', $inputAttrs = [], $wrapperAttrs = [], $errors = []): string
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
        $html .= "<div class='invalid-feedback'>{$errorMsg}</div></div>";
        return $html;
    }

    public static function textareaField($label, $id, $value, $inputAttrs = [], $wrapperAttrs = [], $errors = []): string
    {
        $inputAttrs = self::appendErrors($id, $inputAttrs, $errors);
        $wrapperAttrs['class'] = 'form-group ' . ($wrapperAttrs['class'] ?? '');
        $wrapperStr = self::processAttrs($wrapperAttrs);
        $inputAttrs['class'] = 'form-control ' . ($inputAttrs['class'] ?? '');
        $inputStr = self::processAttrs($inputAttrs);
        $errorMsg = array_key_exists($id, $errors) ? $errors[$id] : '';
        $html = "<div {$wrapperStr}>";
        $html .= "<label for='{$id}'>{$label}</label>";
        $html .= "<textarea id='{$id}' name='{$id}' {$inputStr} placeholder='{$label}'>{$value}</textarea>";
        $html .= "<div class='invalid-feedback'>{$errorMsg}</div></div>";
        return $html;
    }

    public static function fileField($label, $id, $input = [], $wrapper = [], $errors = []): string
    {
        $inputAttrs = self::appendErrors($id, $input, $errors);
        $wrapperAttrs['class'] = 'form-group ' . ($wrapperAttrs['class'] ?? '');
        $wrapperStr = self::processAttrs($wrapperAttrs);
        $inputAttrs['class'] = 'form-control-file ' . ($inputAttrs['class'] ?? '');
        $inputStr = self::processAttrs($inputAttrs);
        $errorMsg = array_key_exists($id, $errors) ? $errors[$id] : '';
        $html = "<div {$wrapperStr}>";
        $html .= "<label for=\"{$id}\">{$label}</label>";
        $html .= "<input type=\"file\" id=\"{$id}\" name=\"{$id}\" {$inputStr} class='form-control-file'/>";
        $html .= "<div class=\"invalid-feedback\">{$errorMsg}</div></div>";
        return $html;
    }

    public static function appendErrors($key, $inputAttrs, $errors)
    {
        if (array_key_exists($key, $errors)) {
            if (array_key_exists('class', $inputAttrs)) {
                $inputAttrs['class'] .= ' is-invalid';
            } else {
                $inputAttrs['class'] = 'is-invalid';
            }
        }
        return $inputAttrs;
    }
}
