<?php

declare(strict_types=1);

/**
 * ==========================================
 * Bolt - BootstrapForm =====================
 * ==========================================
 */

namespace celionatti\Bolt\Forms;


class BootstrapForm extends Form
{
    public static function submitButton($label = 'Submit', $class = '', $attrs = [])
    {
        $attrs['class'] = "btn {$class}";
        return parent::submitBtn($label, $attrs);
    }

    public static function inputField($label, $id, $value, $inputAttrs = [], $wrapperAttrs = [], $errors = []): string
    {
        $inputAttrs = self::appendBootErrors($id, $inputAttrs, $errors);
        $wrapperAttrs['class'] = 'form-group ' . ($wrapperAttrs['class'] ?? '');
        $wrapperStr = self::processAttrs($wrapperAttrs);
        $inputAttrs['class'] = 'form-control ' . ($inputAttrs['class'] ?? '');
        $inputStr = self::processAttrs($inputAttrs);
        $errorMsg = array_key_exists($id, $errors) ? $errors[$id] : '';
        $html = "<div {$wrapperStr}>";
        $html .= "<label for='{$id}' class='form-label'>{$label}</label>";
        $html .= "<input id='{$id}' name='{$id}' value='{$value}' {$inputStr} placeholder='{$label}' />";
        $html .= "<div class='invalid-feedback'>{$errorMsg}</div></div>";
        return $html;
    }

    public static function selectField($label, $id, $value, $options, $inputAttrs = [], $wrapperAttrs = [], $errors = []): string
    {
        $inputAttrs = self::appendBootErrors($id, $inputAttrs, $errors);
        $inputAttrs = self::processAttrs($inputAttrs);
        $wrapperStr = self::processAttrs($wrapperAttrs);
        $errorMsg = array_key_exists($id, $errors) ? $errors[$id] : "";
        $html = "<div {$wrapperStr}>";
        $html .= "<label for='{$id}' class='form-label'>{$label}</label>";
        $html .= "<select id='{$id}' name='{$id}' {$inputAttrs}>";
        $html .= "<option value=''>Please Select...</option>";
        foreach ($options as $val => $display) {
            $selected = $val == $value ? ' selected ' : "";
            $html .= "<option value='{$val}'{$selected}>{$display}</option>";
        }
        $html .= "</select>";
        $html .= "<div class='invalid-feedback'>{$errorMsg}</div></div>";
        return $html;
    }

    public static function checkField($label, $id, $checked = '', $inputAttrs = [], $wrapperAttrs = [], $errors = []): string
    {
        $inputAttrs = self::appendBootErrors($id, $inputAttrs, $errors);
        $wrapperStr = self::processAttrs($wrapperAttrs);
        $inputStr = self::processAttrs($inputAttrs);
        $checkedStr = $checked == 'on' ? "checked" : "";
        $errorMsg = array_key_exists($id, $errors) ? $errors[$id] : "";
        $html = "<div {$wrapperStr}>";
        $html .= "<input type=\"checkbox\" id=\"{$id}\" name=\"{$id}\" {$inputStr} {$checkedStr}>";
        $html .= "<label class=\"form-check-label text-black px-2\" for=\"{$id}\">{$label}</label>";
        $html .= "<div class='invalid-feedback'>{$errorMsg}</div></div>";
        return $html;
    }

    public static function textareaField($label, $id, $value, $inputAttrs = [], $wrapperAttrs = [], $errors = []): string
    {
        $wrapperStr = self::processAttrs($wrapperAttrs);
        $inputAttrs = self::appendBootErrors($id, $inputAttrs, $errors);
        $inputStr = self::processAttrs($inputAttrs);
        $errorMsg = array_key_exists($id, $errors) ? $errors[$id] : '';
        $html = "<div {$wrapperStr}>";
        $html .= "<label for='{$id}' class='form-label'>{$label}</label>";
        $html .= "<textarea id='{$id}' name='{$id}' {$inputStr} placeholder='{$label}'>{$value}</textarea>";
        $html .= "<div class='invalid-feedback'>{$errorMsg}</div></div>";
        return $html;
    }

    public static function fileField($label, $id, $input = [], $wrapperAttrs = [], $errors = []): string
    {
        $wrapperStr = self::processAttrs($wrapperAttrs);
        $inputAttrs = self::appendBootErrors($id, $input, $errors);
        $inputAttrs['class'] = 'form-control-file ' . ($inputAttrs['class'] ?? '');
        $inputStr = self::processAttrs($inputAttrs);
        $errorMsg = array_key_exists($id, $errors) ? $errors[$id] : '';
        $html = "<div {$wrapperStr}>";
        $html .= "<label for=\"{$id}\" class='form-label'>{$label}</label>";
        $html .= "<input type=\"file\" id=\"{$id}\" name=\"{$id}\" {$inputStr} class='form-control-file'/>";
        $html .= "<div class=\"invalid-feedback\">{$errorMsg}</div></div>";
        return $html;
    }

    public static function appendBootErrors($key, $inputAttrs, $errors)
    {
        if (array_key_exists($key, $errors)) {
            if (array_key_exists('class', $inputAttrs)) {
                $inputAttrs['class'] .= ' is-invalid';
            } else {
                $inputAttrs['class'] = ' is-invalid';
            }
        }
        return $inputAttrs;
    }
}
