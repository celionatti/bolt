<?php

class AdvancedForm_a
{
    private $fields = [];
    private $data = [];
    private $errors = [];
    private $tokenFieldName = 'csrf_token';
    private $token;

    public function __construct()
    {
        // Generate and store a CSRF token
        $this->generateCSRFToken();
    }

    public function addField($name, $type = 'text', $label = '', $options = [])
    {
        $field = [
            'name' => $name,
            'type' => $type,
            'label' => $label,
            'options' => $options,
        ];
        $this->fields[$name] = $field;
    }

    public function setFieldData($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function validateRequired($name, $message)
    {
        $value = $this->getFieldValue($name);
        if (empty($value)) {
            $this->addError($name, $message);
        }
    }

    public function validateEmail($name, $message)
    {
        $value = $this->getFieldValue($name);
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($name, $message);
        }
    }

    public function validateMinLength($name, $minLength, $message)
    {
        $value = $this->getFieldValue($name);
        if (strlen($value) < $minLength) {
            $this->addError($name, $message);
        }
    }

    public function validateMaxLength($name, $maxLength, $message)
    {
        $value = $this->getFieldValue($name);
        if (strlen($value) > $maxLength) {
            $this->addError($name, $message);
        }
    }

    public function validateFileUpload($name, $allowedTypes, $maxFileSize, $message)
    {
        if (!isset($_FILES[$name])) {
            $this->addError($name, $message);
            return;
        }

        $file = $_FILES[$name];
        $fileType = mime_content_type($file['tmp_name']);
        $fileSize = $file['size'];

        if (!in_array($fileType, $allowedTypes) || $fileSize > $maxFileSize) {
            $this->addError($name, $message);
        }
    }

    public function generateCSRFToken()
    {
        $this->token = bin2hex(random_bytes(32));
        $_SESSION[$this->tokenFieldName] = $this->token;
    }

    public function validateCSRFToken($tokenName = 'csrf_token', $message = 'CSRF validation failed.')
    {
        if (empty($_SESSION[$tokenName]) || empty($_POST[$tokenName]) || $_SESSION[$tokenName] !== $_POST[$tokenName]) {
            $this->addError($tokenName, $message);
        }
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function renderErrors()
    {
        $html = '<ul class="error-list">';
        foreach ($this->errors as $error) {
            $html .= '<li>' . $error . '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    public function renderField($name)
    {
        if (!isset($this->fields[$name])) {
            return '';
        }

        $field = $this->fields[$name];
        $value = $this->getFieldValue($name);
        $type = $field['type'];
        $label = $field['label'];
        $options = $field['options'];

        $input = '';

        switch ($type) {
            case 'text':
                $input = '<input type="text" name="' . $name . '" value="' . htmlspecialchars($value) . '" ' . $this->renderOptions($options) . '>';
                break;
            case 'textarea':
                $input = '<textarea name="' . $name . '" ' . $this->renderOptions($options) . '>' . htmlspecialchars($value) . '</textarea>';
                break;
            case 'select':
                $input = '<select name="' . $name . '" ' . $this->renderOptions($options) . '>';
                foreach ($options['options'] as $optionValue => $optionLabel) {
                    $selected = $value == $optionValue ? 'selected' : '';
                    $input .= '<option value="' . $optionValue . '" ' . $selected . '>' . $optionLabel . '</option>';
                }
                $input .= '</select>';
                break;
            case 'checkbox':
                $checked = $value ? 'checked' : '';
                $input = '<input type="checkbox" name="' . $name . '" ' . $checked . ' ' . $this->renderOptions($options) . '>';
                break;
            case 'radio':
                $input = '';
                foreach ($options['options'] as $optionValue => $optionLabel) {
                    $checked = $value == $optionValue ? 'checked' : '';
                    $input .= '<label><input type="radio" name="' . $name . '" value="' . $optionValue . '" ' . $checked . '>'
                        . $optionLabel . '</label>';
                }
                break;
        }

        return '<label>' . $label . '</label>' . $input;
    }

    public function renderForm()
    {
        $html = '<form method="post" enctype="multipart/form-data">';
        foreach ($this->fields as $fieldName => $field) {
            $html .= $this->renderField($fieldName);
        }
        $html .= $this->renderCSRFTokenField();
        $html .= '<button type="submit">Submit</button>';
        $html .= '</form>';
        return $html;
    }

    private function getFieldValue($name)
    {
        return $this->data[$name] ?? '';
    }

    private function addError($field, $message)
    {
        $this->errors[$field] = $message;
    }

    private function renderOptions($options)
    {
        $attributes = '';
        foreach ($options as $key => $value) {
            if ($key !== 'options') {
                $attributes .= $key . '="' . $value . '" ';
            }
        }
        return $attributes;
    }

    private function renderCSRFTokenField()
    {
        return '<input type="hidden" name="' . $this->tokenFieldName . '" value="' . $this->token . '">';
    }
}


/**
 * Usage
 */

 // Create a new form instance
$form = new AdvancedForm();

// Define form fields
$form->addField('name', 'text', 'Name', ['required' => true]);
$form->addField('email', 'text', 'Email', ['required' => true, 'email' => true]);

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Populate form data
    $form->setFieldData('name', $_POST['name']);
    $form->setFieldData('email', $_POST['email']);

    // Validate form data
    $form->validateRequired('name', 'Name is required.');
    $form->validateEmail('email', 'Invalid email address.');

    if (!$form->hasErrors()) {
        // Form data is valid, handle it (e.g., save to the database)
        $name = $form->getFieldValue('name');
        $email = $form->getFieldValue('email');
        // Handle the data (e.g., save it to the database)
    }
}

// Render the form
echo $form->renderForm();
