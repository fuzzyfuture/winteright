<?php

namespace App\Validators;

use Illuminate\Contracts\Support\MessageProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Validator;

abstract class ValidatorWrapper implements MessageProvider
{
    protected array $rules = [];
    protected array $validators = [];
    protected array $sanitizers = [];
    protected array $data = [];
    protected ?MessageBag $errors;

    public function __construct()
    {
        $this->errors = new MessageBag();
    }

    /**
     * Validates the data against the defined validation rules.
     *
     * @param array|Collection $data The data to be validated.
     * @param string $ruleset The ruleset for the data to be validated against.
     * @return bool True if the data is valid.
     */
    public function validate(array|Collection $data, string $ruleset = 'create'): bool
    {
        if ($data instanceof Collection) {
            $data = $data->toArray();
        }

        $data = $this->runSanitizers($data);
        $rules = $this->rules[$ruleset];

        if ($rules && is_array($rules) && count($rules) > 0) {
            $validator = Validator::make($data, $rules);

            if (!$result = $validator->passes()) {
                $this->errors = $validator->errors();
            }
        } else {
            $result = true;
        }

        if (!$this->runValidators($data, $ruleset)) {
            $result = false;
        }

        $this->data = $data;

        return $result;
    }

    /**
     * Sanitizes the data using the defined sanitizer methods.
     *
     * @param array $data The data to be sanitized.
     * @return array The sanitized data.
     */
    protected function runSanitizers(array $data): array
    {
        foreach ($this->sanitizers as $sanitizer)
        {
            $method = 'sanitize' . Str::studly($sanitizer);

            if (isSet($data[$sanitizer]) && method_exists($this, $method)) {
                $data = call_user_func([$this, $method], $data);
            }
        }

        return $data;
    }

    /**
     * Validates the data using the defined validator methods.
     *
     * @param array $data The data to be validated.
     * @param string $ruleset The name of the ruleset for the data to be validated against.
     * @return bool True if the validation was successful.
     */
    protected function runValidators(array $data, string $ruleset): bool
    {
        $result = true;

        if (!isset($this->validators[$ruleset])) {
            return true;
        }

        foreach ($this->validators[$ruleset] as $validator) {
            $method = 'validate' . Str::studly($validator);

            if (isSet($data[$ruleset]) && method_exists($this, $method)) {
                if (!call_user_func([$this, $method], $data)) {
                    $result = false;
                }
            } else {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Retrieves data for this validator, sanitized if validate() has been called. Retrieves the data with the
     * specified key, or all data if no key is specified.
     *
     * @param string|null $key The key to retrieve data for. If null (default), all data is retrieved.
     * @return mixed All data if no key is specified. The data at the specified key, if the key exists. Null if the
     * specified key does not exist.
     */
    public function getData(string|null $key = null): mixed
    {
        if (is_null($key)) {
            return $this->data;
        }

        return $this->data[$key] ?? null;
    }

    /**
     * Retrieves the error message bag for this validator.
     *
     * @return MessageBag The error message bag.
     */
    public function getMessageBag(): MessageBag
    {
        return $this->errors;
    }
}
