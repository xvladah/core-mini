<?php

class FormEngine
{
    const string GLOBAL = '_';

    private array $schema;
    private array $data = [];
    private array $errors = [];

    public function __construct(array $schema, array $data = [])
    {
        $this->schema   = $schema;
        $this->data     = $data;
    }

    public function validate(array $input): bool
    {
        $this->data = $input;

        foreach ($this->schema as $name => $field) {

            $v = trim($input[$name] ?? '');

            if (($field['required'] ?? false) && $v === '') {
                $this->errors[$name] = [
                    'text' => __('err.invalid_value_required', 'Invalid value, is required')
                ];
                continue;
            }

            if ($v === '') continue;

            if (($field['type'] ?? '') === 'email') {

                if (!filter_var($v,FILTER_VALIDATE_EMAIL))
                    $this->errors[$name] = [
                        'text' => __('err.invalid_email', 'Invalid email')
                    ];
            }

            if (($field['type'] ?? '') === 'emailList') {

                $items = preg_split('/[;,]+/', $v);

                foreach ($items as $email) {

                    $email = trim($email);

                    if ($email === '')
                        continue;

                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $this->errors[$name] = [
                            'text' => __('err.invalid_email_list', 'Invalid email list')
                        ];
                        break;
                    }
                }
            }

            if (($field['type'] ?? '') === 'phone' || ($field['type'] ?? '') === 'mobile') {

                if (!preg_match('/^\+?[0-9\s\-]{6,20}$/', $v)) {
                    $this->errors[$name] = [
                        'text' => __('err.invalid_phone', 'Invalid phone/mobile number')
                    ];
                }
            }

            if (($field['type'] ?? '') === 'phoneList' || ($field['type'] ?? '') === 'mobileList') {

                $items = preg_split('/[;,]+/', $v);

                foreach ($items as $phone) {

                    $phone = trim($phone);

                    if ($phone === '')
                        continue;

                    if (!preg_match('/^\+?[0-9\s\-]{6,20}$/', $phone)) {
                        $this->errors[$name] = [
                            'text' => __('err.invalid_phone_list', 'Invalid phone/mobile list')
                        ];
                        break;
                    }
                }
            }

            if (($field['type'] ?? '') === 'login') {

                if (!preg_match('/^[a-zA-Z0-9._-]{3,60}$/', $v)) {
                    $this->errors[$name] = [
                        'text' => __('err.invalid_login', 'Invalid login')
                    ];
                }
            }

            if (($field['type'] ?? '') === 'password') {

                if (strlen($v) < 8 ||
                    !preg_match('/[a-zA-Z]/', $v) ||
                    !preg_match('/[0-9]/', $v)) {

                    $this->errors[$name] = [
                        'text' => __('err.weak_password', 'Weak password')
                    ];
                }
            }

            if (($field['type'] ?? '') === 'int') {

                if (!filter_var($v,FILTER_VALIDATE_INT))
                    $this->errors[$name] = [
                        'text' => __('err.whole_number', 'Must be a whole number')
                    ];

                $i = (int)$v;

                if(isset($field['min']) && $i < $field['min'])
                    $this->errors[$name] = [
                        'text' => __('err.too_small', 'Too small')
                    ];

                if(isset($field['max']) && $i > $field['max'])
                    $this->errors[$name] = [
                        'text' => __('err.too_big', 'Too big')
                    ];
            }

            if (($field['type'] ?? '') === 'float') {

                if (!filter_var($v,FILTER_VALIDATE_FLOAT))
                    $this->errors[$name] = [
                        'text' => __('err.float_number', 'Must be a float number')
                    ];

                $i = (int)$v;

                if(isset($field['min']) && $i < $field['min'])
                    $this->errors[$name] = [
                        'text' => __('err.too_small', 'Too small')
                    ];

                if(isset($field['max']) && $i > $field['max'])
                    $this->errors[$name] = [
                        'text' => __('err.too_big', 'Too big')
                    ];
            }

            if(isset($field['min']) && strlen($v) < $field['min'])
                $this->errors[$name] = [
                    'text' => sprintf(__('err.min_length', 'Min. %s characters'), $field['min'])
                ];

            if(isset($field['max']) && strlen($v) > $field['max'])
                $this->errors[$name] = [
                    'text' => sprintf(__('err.max_length', 'Max. %s characters'), $field['max'])
                ];

            if(($field['type'] ?? '') === 'select') {

                if(!array_key_exists($v,$field['options']))
                    $this->errors[$name] = [
                        'text' => __('err.bad_value', 'Invalid value')
                    ];
            }

        }

        return empty($this->errors);
    }

    public function addError(string $error, string $name = self::GLOBAL): void
    {
        $this->errors[$name] = $error;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function schema(): array
    {
        return $this->schema;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function getLabel(string $name): string
    {
        return $this->schema[$name]['label'] ?? '';
    }

    public function getLabelId(string $name): string
    {
        return $this->schema[$name]['labelId'] ?? '';
    }

    public function getDesc(string $name): string
    {
        return $this->schema[$name]['desc'] ?? '';
    }

    public function getDescId(string $name): string
    {
        return $this->schema[$name]['descId'] ?? '';
    }

    public function getType(string $name): string
    {
        return $this->schema[$name]['type'] ?? '';
    }

    public function buttons(string $name) :array
    {
        return $this->schema[$name]['buttons'] ?? [];
    }

    public function getButtonTitle(string $name, string $buttonId): string
    {
        return $this->schema[$name]['buttons'][$buttonId]['title'] ?? '';
    }

    public function renderInput(string $name): string
    {
        if(array_key_exists($name, $this->schema)) {

            $f = $this->schema[$name];

            if (isset($this->data[$name]) && $this->data[$name] != '' && $this->data[$name] != 'undefined') {
                $v = htmlspecialchars($this->data[$name]);
            } else {
                $v = '';
            }

            $class = 'form-control';

            if (isset($this->errors[$name]))
                $class .= ' is-invalid';

            if (isset($f['class'])) {
                if ($f['class'] != '')
                    $class .= ' ' . $f['class'];
                else
                    $class = '';
            }

            if ($class != '')
                $class = ' class="' . $class . '"';

            if ($f['type'] === 'select') {

                $html = '<select' . $class . ' id="' . $name . '" name="' . $name . '">';

                foreach ($f['options'] as $k => $t) {
                    $selected = $k == $v ? ' selected' : '';
                    $html .= '<option value="' . $k . '"' . $selected . '>' . $t . '</option>';
                }

                $html .= '</select>';

                return $html;
            }

            $placeholder = isset($f['placeholder']) ? ' placeholder="' . $f['placeholder'] . '"' : '';
            $maxlength = isset($f['maxlength']) ? ' maxlength="' . $f['maxlength'] . '"' : '';
            $readonly = isset($f['readonly']) ? ' readonly="readonly"' : '';
            $disabled = isset($f['disabled']) ? ' disabled="disabled"' : '';

            if ($f['type'] === 'textarea') {

                $rows = isset($f['rows']) ? ' rows="' . $f['rows'] . '"' : '';
                $cols = isset($f['cols']) ? ' cols="' . $f['cols'] . '"' : '';

                return '<textarea' . $class . ' id="' . $name . '" name="' . $name . '"' . $rows . $cols . $placeholder . $readonly . $disabled . '>' . $v . '</textarea>';
            }

            if ($f['type'] === 'password') {
                return '<input' . $class . ' type="password" id="' . $name . '" name="' . $name . '"' . $placeholder . $maxlength . '>';
            }

            if ($f['type'] === 'checkbox') {
                return '<input' . $class . ' type="checkbox" id="' . $name . '" name="' . $name . '" value="1" ' . ($v == '1' ? 'checked' : '') . $readonly . $disabled . '>';
            }

            if ($f['type'] === 'radio') {
                return '<input' . $class . ' type="radio" id="' . $name . '" name="' . $name . '" value="1" ' . ($v == '1' ? 'checked' : '') . $readonly . $disabled . '>';
            }

            if ($f['type'] === 'email') {
                return '<input' . $class . ' type="email" id="' . $name . '" name="' . $name . '" value="' . $v . '"' . $placeholder . $maxlength . $readonly . $disabled . '>';
            }

            if ($f['type'] === 'date') {
                return '<input' . $class . ' type="date" id="' . $name . '" name="' . $name . '" value="' . $v . '" ' . $readonly . $disabled .'>';
            }

            if ($f['type'] === 'datetime') {
                return '<input' . $class . ' type="datetime-local" id="' . $name . '" name="' . $name . '" value="' . $v . '" ' . $readonly . $disabled .'>';
            }

            if ($f['type'] === 'time') {
                return '<input' . $class . ' type="time" id="' . $name . '" name="' . $name . '" value="' . $v . '" ' . $readonly . $disabled .'>';
            }

            if ($f['type'] === 'number') {
                return '<input' . $class . ' type="number" id="' . $name . '" name="' . $name . '" value="' . $v . '" ' . $readonly . $disabled .'>';
            }

            if ($f['type'] === 'url') {
                return '<input' . $class . ' type="url" id="' . $name . '" name="' . $name . '" value="' . $v . '"' . $placeholder . $maxlength . $readonly . $disabled . '>';
            }

            if ($f['type'] === 'tel') {
                return '<input' . $class . ' type="tel" id="' . $name . '" name="' . $name . '" value="' . $v . '"' . $placeholder . $maxlength . $readonly . $disabled . '>';
            }

            if ($f['type'] === 'file') {
                return '<input' . $class . ' type="file" id="' . $name . '" name="' . $name . '"' . $readonly . $disabled .'>';
            }

            if ($f['type'] === 'color') {
                return '<input' . $class . ' type="color" id="' . $name . '" name="' . $name . '" value="' . $v . '"' . $readonly . $disabled .'>';
            }

            if ($f['type'] === 'search') {
                return '<input' . $class . ' type="search" id="' . $name . '" name="' . $name . '" value="' . $v . '"' . $placeholder . $maxlength . $readonly . $disabled . '>';
            }

            if ($f['type'] === 'hidden') {
                return '<input' . $class . ' type="hidden" id="' . $name . '" name="' . $name . '" value="' . $v . '"' . $placeholder . $maxlength . $readonly . $disabled . '>';
            }

            return '<input' . $class . ' type="text" id="' . $name . '" name="' . $name . '" value="' . $v . '"' . $placeholder . $maxlength . $readonly . $disabled . '>';
        } else
            return '';
    }

    public function isRequired(string $name): bool
    {
        return isset($this->schema[$name]['required']) && $this->schema[$name]['required'];
    }

    public function isDisabled(string $name): bool
    {
        return isset($this->schema[$name]['disabled']) && $this->schema[$name]['disabled'];
    }

    public function isReadonly(string $name): bool
    {
        return isset($this->schema[$name]['readonly']) && $this->schema[$name]['readonly'];
    }

    public static function isSubmitted(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] == 'POST');
    }

    public function loadParamValues(): void
    {
        foreach($this->schema as $name => $field) {
            if(isset($_GET[$name]))
                $this->data[$name] = $_GET[$name];
            else
                if(isset($_COOKIE[$name]))
                    $this->data[$name] = TCookies::get($name);
                else
                    if(isset($field['default']))
                        $this->data[$name] = $field['default'];
        }
    }

    public function loadValues(bool $all = false): void
    {
        foreach($this->schema as $name => $field) {
            if(isset($field['value'])) {
                $this->data[$name] = $field['value'];
            } else
                if($all) {
                    $this->data[$name] = '';
                }
        }
    }

    public function loadDefaults(bool $all = false): void
    {
        foreach($this->schema as $name => $field) {
            if(isset($field['default'])) {
                $this->data[$name] = $field['default'];
            } else
                if($all) {
                    $this->data[$name] = '';
                }
        }
    }
}