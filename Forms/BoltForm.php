<?php

declare(strict_types=1);

/**
 * ==========================================
 * Bolt - BoltForm ==========================
 * ==========================================
 */

namespace celionatti\Bolt\Forms;

use celionatti\Bolt\Helpers\CSRF\Csrf;

class BoltForm
{
    protected array $form = [];

    public static function create(): self
    {
        return new self();
    }

    public function openForm(string $action, string $method = 'POST', string $enctype = null, array $attrs = []): self
    {
        $enctypeAttribute = $enctype ? ' enctype="' . $enctype . '"' : '';
        $this->form[] = "<form action='" . htmlspecialchars($action) . "' method='$method'$enctypeAttribute" . self::processAttrs($attrs) . '>';
        return $this;
    }

    public function closeForm(): self
    {
        $this->form[] = '</form>';
        return $this;
    }

    public function csrfField(): self
    {
        $csrf = new Csrf();
        $token = $csrf->generateToken() ?? "";
        $this->form[] = "<input type='hidden' name='_csrf_token' value='{$token}'>";
        return $this;
    }

    public function method(string $method): self
    {
        $this->form[] = "<input type='hidden' value='{$method}' name='_method' />";
        return $this;
    }

    public function input(string $name, string $label, string $value = '', array $attrs = []): self
    {
        $this->form[] = "<label for='$name'>$label</label>";
        $this->form[] = "<input type='text' id='$name' name='$name' value='$value'" . self::processAttrs($attrs) . ' />';
        return $this;
    }

    public function select(string $name, string $label, array $options = [], string $selected = '', array $attrs = []): self
    {
        $html = "<label for='$name'>$label</label>";
        $html .= "<select id='$name' name='$name'" . self::processAttrs($attrs) . '>';
        foreach ($options as $value => $text) {
            $isSelected = ($value == $selected) ? 'selected' : '';
            $html .= "<option value='$value' $isSelected>$text</option>";
        }
        $html .= '</select>';
        $this->form[] = $html;
        return $this;
    }

    public function textarea(string $name, string $label, string $value = '', array $attrs = []): self
    {
        $this->form[] = "<label for='$name'>$label</label>";
        $this->form[] = "<textarea id='$name' name='$name'" . self::processAttrs($attrs) . ">$value</textarea>";
        return $this;
    }

    public function checkbox(string $name, string $label, bool $checked = false, array $attrs = []): self
    {
        $checkedAttr = $checked ? 'checked' : '';
        $this->form[] = "<input type='checkbox' id='$name' name='$name' value='1' $checkedAttr" . self::processAttrs($attrs) . ">";
        $this->form[] = "<label for='$name'>$label</label>";
        return $this;
    }

    public function submitBtn(string $text, array $attrs = []): self
    {
        $this->form[] = "<button type='submit'" . self::processAttrs($attrs) . ">$text</button>";
        return $this;
    }

    public function file(string $name, string $label, array $attrs = []): self
    {
        $this->form[] = "<label for='$name'>$label</label>";
        $this->form[] = "<input type='file' id='$name' name='$name'" . self::processAttrs($attrs) . ' />';
        return $this;
    }

    public function addCustomHtml(string $html): self
    {
        $this->form[] = $html;
        return $this;
    }

    public function render(): string
    {
        return implode('', $this->form);
    }

    protected static function processAttrs(array $attrs): string
    {
        return array_reduce(array_keys($attrs), fn($carry, $key) => $carry . " $key='{$attrs[$key]}'", '');
    }
}
