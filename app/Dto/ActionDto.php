<?php

namespace App\Dto;

use Illuminate\Http\UploadedFile;

class ActionDto
{
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
    public $body;

    /**
     * @var array<UploadedFile>
     */
    public $images;

    /**
     * @var int
     */
    public $krCurrentValue;

    /**
     * @var int
     */
    public $krNewValue;

}