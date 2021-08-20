<?php

namespace App\Dto;

use Illuminate\Http\UploadedFile;

class UserDto
{
    /**
     * @var int
     */
    public $id;
    
    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $bio;

    /**
     * @var string
     */
    public $timezone;

    /**
     * @var UploadedFile
     */
    public $avatar;

    /**
     * @var bool
     */
    public $defaultTeamId;

    /**
     * @var bool
     */
    public $emailVarified;

}