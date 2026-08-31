<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Exercise\ValueObject;

use App\Localingo\Domain\Exercise\Exception\ExerciseMissingStateOrder;

class ExerciseState
{
    private const string STATE_NEW = 'new';
    private const string STATE_FAILED = 'failed';
    private const string STATE_OPEN = 'open';
    private const string STATE_DONE = 'done';

    /** @var list<string> */
    private const array STATES_ORDER = [
        self::STATE_FAILED,
        self::STATE_OPEN,
        self::STATE_DONE,
    ];

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function new(): self
    {
        return new self(self::STATE_NEW);
    }

    public function isNew(): bool
    {
        return $this->value === self::STATE_NEW;
    }

    public static function failed(): self
    {
        return new self(self::STATE_FAILED);
    }

    public function isFailed(): bool
    {
        return $this->value === self::STATE_FAILED;
    }

    public static function open(): self
    {
        return new self(self::STATE_OPEN);
    }

    public function isOpen(): bool
    {
        return $this->value === self::STATE_OPEN;
    }

    public static function done(): self
    {
        return new self(self::STATE_DONE);
    }

    public function isDone(): bool
    {
        return $this->value === self::STATE_DONE;
    }

    /**
     * @throws ExerciseMissingStateOrder
     */
    public function previous(): self
    {
        $key = array_search($this->value, self::STATES_ORDER, true);
        if (!is_int($key)) {
            throw new ExerciseMissingStateOrder();
        }

        $previous = $key - 1;
        $value = $previous >= 0 ? self::STATES_ORDER[$previous] : $this->value;

        return new self($value);
    }

    /**
     * @throws ExerciseMissingStateOrder
     */
    public function next(): self
    {
        // 'New' state should only happen once.
        if ($this->value === self::STATE_NEW) {
            return self::open();
        }

        $key = array_search($this->value, self::STATES_ORDER, true);
        if (!is_int($key)) {
            throw new ExerciseMissingStateOrder();
        }

        $next = $key + 1;
        $value = $next < count(self::STATES_ORDER) ? self::STATES_ORDER[$next] : $this->value;

        return new self($value);
    }
}
