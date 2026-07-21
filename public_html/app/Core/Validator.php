<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Small rule-based validator. Rules: required, email, max:N, min:N, phone, url, in:a,b,c.
 */
final class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /** @param array<string,string> $rules field => "rule|rule:arg" */
    public function validate(array $rules): bool
    {
        foreach ($rules as $field => $ruleString) {
            $value = isset($this->data[$field]) && is_string($this->data[$field])
                ? trim($this->data[$field])
                : '';
            foreach (explode('|', $ruleString) as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }
        return $this->errors === [];
    }

    private function applyRule(string $field, string $value, string $rule): void
    {
        [$name, $arg] = array_pad(explode(':', $rule, 2), 2, null);

        // Skip optional empty fields for everything except "required".
        if ($value === '' && $name !== 'required') {
            return;
        }

        switch ($name) {
            case 'required':
                if ($value === '') {
                    $this->fail($field, 'This field is required.');
                }
                break;
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->fail($field, 'Enter a valid email address.');
                }
                break;
            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->fail($field, 'Enter a valid URL.');
                }
                break;
            case 'phone':
                if (!preg_match('/^[0-9 +().\-]{6,20}$/', $value)) {
                    $this->fail($field, 'Enter a valid phone number.');
                }
                break;
            case 'max':
                if (mb_strlen($value) > (int) $arg) {
                    $this->fail($field, "Must be at most {$arg} characters.");
                }
                break;
            case 'min':
                if (mb_strlen($value) < (int) $arg) {
                    $this->fail($field, "Must be at least {$arg} characters.");
                }
                break;
            case 'in':
                $allowed = explode(',', (string) $arg);
                if (!in_array($value, $allowed, true)) {
                    $this->fail($field, 'Invalid selection.');
                }
                break;
        }
    }

    private function fail(string $field, string $message): void
    {
        $this->errors[$field] ??= $message;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        return $this->errors === [] ? null : reset($this->errors);
    }
}
