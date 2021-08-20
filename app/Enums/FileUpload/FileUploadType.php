<?php

declare(strict_types=1);

namespace App\Enums\FileUpload;

abstract class FileUploadType
{
    const IMAGE_ACTION = 0;
    const IMAGE_POST = 1;
    const IMAGE_USER_AVATAR = 2;
    const IMAGE_TEAM_AVATAR = 3;
    const IMAGE_MESSAGE = 4;

    const VIDEO_POST = 5;
    const VIDEO_MESSAGE = 6;

    const FILE_ATTACHED_ACTION = 7;
    const FILE_ATTACHED_POST = 8;
    const FILE_ATTACHED_MESSAGE = 9;
}
