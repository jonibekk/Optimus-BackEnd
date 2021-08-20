<?php

namespace App\Dto;

class AttachedFileDto
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
    public $filename;

    /**
     * @var string
     */
    public $fileExt;

    /**
     * @var string
     */
    public $fileUrl;

    /**
     * @var int
     */
    public $fileSize;
    /**
     * @var int
     */
    public $fileType;

    // To Array
    public function toArray()
    {
        return [
            'filename' => $this->id,
            'filename' => $this->filename,
            'file_ext' => $this->fileExt,
            'file_url' => $this->fileUrl,
            'file_size' => $this->fileSize,
            'file_type' => $this->fileType,
        ];
    }

}
