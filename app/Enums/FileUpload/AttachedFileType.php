<?php

declare(strict_types=1);

namespace App\Enums\FileUpload;

abstract class AttachedFileType
{
    const TYPE_MEDIA = 0; // Image and/or Video type;
    const TYPE_FILE = 1;  // Any type other than 'TYPE_MEDIA';
}
