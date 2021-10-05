<?php

namespace App\Model;

class Filter
{
    public const OPERATOR_EQUALS = '=';
    public const OPERATOR_NOT_EQUALS = '!=';

    /**
     * @param Filter::OPERATOR_* $operator
     */
    public function __construct(
        private string $field,
        private string $operator,
        private bool|int|string|float $value,
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

    public function getValue(): bool|int|string|float
    {
        return $this->value;
    }
}
