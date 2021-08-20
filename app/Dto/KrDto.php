<?php

namespace App\Dto;

class KrDto
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $userId;

    /**
     * @var int
     */
    public $goalId;

    /**
     * @var int
     */
    public $krUnit;

    /**
     * @var int
     */
    public $krCurrencyType;

    /**
     * @var string
     */
    public $name;

    /**
     * @var float
     */
    public $progress;

    /**
     * @var float
     */
    public $startValue;

    /**
     * @var float
     */
    public $currentValue;

    /**
     * @var float
     */
    public $targetValue;
}
