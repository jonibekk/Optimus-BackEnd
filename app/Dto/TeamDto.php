<?php

namespace App\Dto;

use Illuminate\Http\UploadedFile;

class TeamDto
{
    /**
     * @var int
     */
    public $id;
    
    /**
     * @var string
     */
    public $name;

    /**
     * @var float
     */
    public $timezone;

    /**
     * @var string
     */
    public $serviceStatus;

    /**
     * @var int
     */
    public $serviceUseType;

    /**
     * @var string
     */
    public $logoUrl;

    /**
     * @var UploadedFile
     */
    public $uploadedImage;

    /**
     * @var string
     */
    public $serviceStartDate;

    /**
     * @var string
     */
    public $serviceEndDate;

}