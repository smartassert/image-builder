<?php

namespace App\Model;

class Filter
{
    public const OPERATOR_EQUALS = '=';
    public const OPERATOR_NOT_CONTAINS = '!contains';
    public const OPERATOR_DEFAULT = self::OPERATOR_EQUALS;

    private const KEY_FIELD = 'field';
    private const KEY_OPERATOR = 'operator';
    private const KEY_VALUE = 'value';

    /**
     * @param Filter::OPERATOR_* $operator
     */
    public function __construct(
        private string $field,
        private string $operator,
        private bool | int | string | float $value,
    ) {
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getValue(): bool | int | string | float
    {
        return $this->value;
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): ?self
    {
        $field = $data[self::KEY_FIELD] ?? null;
        $operator = $data[self::KEY_OPERATOR] ?? self::OPERATOR_DEFAULT;
        $value = $data[self::KEY_VALUE] ?? null;

        $fieldIsValid = is_string($field);
        $operatorIsValid = in_array(
            $operator,
            [
                self::OPERATOR_EQUALS,
                self::OPERATOR_NOT_CONTAINS
            ]
        );

        $valueIsValid = is_bool($value) || is_int($value) || is_string($value) || is_float($value);

        return $fieldIsValid && $operatorIsValid && $valueIsValid
            ? new Filter($field, $operator, $value)
            : null;
    }
}
