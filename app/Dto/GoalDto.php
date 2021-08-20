<?php

namespace App\Dto;

class GoalDto
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
    public $teamId;

    /**
     * @var string
     */
    public $name;

    /**
     * @var ?string
     */
    public $description;

    /**
     * @var ?string
     */
    public $dueDate;

    /**
     * @var ?string
     */
    public $color;

    /**
     * @var float
     */
    public $progress;

}
