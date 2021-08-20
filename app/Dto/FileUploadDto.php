<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enums\FileUpload\FileUploadType;

class FileUploadDto
{
    public function getFilePath(int $type, int $id): string
    {
        switch ($type) {
            case FileUploadType::IMAGE_ACTION:
                return $this->getActionImagePath($id);
                
            case FileUploadType::IMAGE_POST:
                return $this->getPostImagePath($id);
                
            case FileUploadType::IMAGE_USER_AVATAR:
                return $this->getUserAvatarImagePath($id);
                
            case FileUploadType::IMAGE_TEAM_AVATAR:
                return $this->getTeamAvatarImagePath($id);
                
            case FileUploadType::IMAGE_MESSAGE:
                return $this->getChannelImagePath($id);
                
            case FileUploadType::VIDEO_POST:
                return $this->getPostVideoPath($id);

            case FileUploadType::VIDEO_MESSAGE:
                return $this->getChannelVideoPath($id);

            case FileUploadType::FILE_ATTACHED_ACTION:
                return $this->getActionFilePath($id);

            case FileUploadType::FILE_ATTACHED_POST:
                return $this->getPostFilePath($id);

            case FileUploadType::FILE_ATTACHED_MESSAGE:
                return $this->getChannelFilePath($id);
        }
    }

    public function getActionImagePath(int $actionId): string
    {
        return 'Images/Actions/'.$actionId;
    }
    
    public function getPostImagePath(int $postId): string
    {
        return 'Images/Posts/'.$postId;
    }

    public function getUserAvatarImagePath(int $userId): string
    {
        return 'Images/Avatars/user/'.$userId;
    }

    public function getTeamAvatarImagePath(int $teamId): string
    {
        return 'Images/Avatars/team/'. $teamId;
    }

    public function getChannelImagePath(int $messageRoomId): string
    {
        return 'Images/Messages/'. $messageRoomId;
    }

    public function getPostVideoPath(int $postId): string
    {
        return 'Videos/Posts/'.$postId;
    }

    public function getChannelVideoPath(int $messageRoomId): string
    {
        return 'Videos/Channels/'. $messageRoomId;
    }

    public function getActionFilePath(int $actionId): string
    {
        return 'Files/Actions/'.$actionId;
    }

    public function getPostFilePath(int $postId): string
    {
        return 'Files/Posts/'.$postId;
    }

    public function getChannelFilePath(int $channelId): string
    {
        return 'Files/Channels/'.$channelId;
    }

}
