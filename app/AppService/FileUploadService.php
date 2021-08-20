<?php

namespace App\AppService;

use App\Dto\AttachedFileDto;
use App\Dto\FileUploadDto;
use App\Models\AttachedFiles;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{

    // $relatedId -> can be [action_id, user_id, team_id, post_id, etc.] 
    public function uploadToS3(int $type, int $relatedId, UploadedFile $file): AttachedFileDto
    {
        $fileUpload = new FileUploadDto();
        $attachedFileData = new AttachedFileDto();

        $path = $fileUpload->getFilePath($type, $relatedId);
        $path = $file->store($path, 's3');

        if (Storage::disk('s3')->exists($path)) {
            /** @var \Illuminate\Filesystem\FilesystemManager $disk */
            $disk = Storage::disk('s3');
            $attachedFileData->fileUrl = $disk->url($path);
            $attachedFileData->filename = $file->getClientOriginalName();
            $attachedFileData->fileExt = $file->getClientOriginalExtension();
            $attachedFileData->fileSize = $file->getSize();
        }

        return $attachedFileData;
    }

    // Store attached files
    public function storeAttachedFiles(AttachedFileDto $fileData)
    {
        $newFile = new AttachedFiles([
            'user_id' => $fileData->userId,
            'team_id' => $fileData->teamId,
            'filename' => $fileData->filename,
            'file_ext' => $fileData->fileExt,
            'file_url' => $fileData->fileUrl,
            'file_size' => $fileData->fileSize,
            'file_type' => $fileData->fileType,
        ]);
        $newFile->save();

        return $newFile->id;
    }

}