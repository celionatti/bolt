<?php

class DynamicForm_jh extends AdvancedForm
{
    private $fieldClasses = [];

    public function setFieldClass($name, $class)
    {
        $this->fieldClasses[$name] = $class;
    }

    public function textField($name, $label, $options = [])
    {
        $this->addField($name, 'text', $label, $options);
    }

    public function textareaField($name, $label, $options = [])
    {
        $this->addField($name, 'textarea', $label, $options);
    }

    public function selectField($name, $label, $options = [])
    {
        $this->addField($name, 'select', $label, $options);
    }

    public function checkboxField($name, $label, $options = [])
    {
        $this->addField($name, 'checkbox', $label, $options);
    }

    public function radioField($name, $label, $options = [])
    {
        $this->addField($name, 'radio', $label, $options);
    }

    protected function renderField($name)
    {
        $fieldHtml = parent::renderField($name);
        $class = $this->fieldClasses[$name] ?? '';

        if (!empty($class)) {
            $fieldHtml = str_replace('<' . $name, '<' . $name . ' class="' . $class . '"', $fieldHtml);
        }

        return $fieldHtml;
    }
}
