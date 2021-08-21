<?php

namespace App\AppService;

use App\Dto\ActionDto;
use App\Enums\FileUpload\AttachedFileType;
use App\Enums\FileUpload\FileUploadType;
use App\Enums\Likes\LikableType;
use App\Models\ActionFiles;
use App\Models\ActionPosts;
use App\Models\KeyResults;
use App\Models\Likable;
use App\Models\Like;
use Illuminate\Support\Facades\DB;
use Exception;

class ActionsService
{
    // Get all actions of user in a team
    public function getUserActions(int $teamId, int $userId)
    {
        return ActionPosts::where('team_id', $teamId)->where('user_id', $userId)->get();
    }

    // Get all actions in key-result
    public function getKrActions(int $krId)
    {
        return KeyResults::find($krId)->action;
    }

    // Create an Action
    public function createAction(int $goalId, int $krId, ActionDto $actionData, array $uploadedImages)
    {
        $fileUploadService = new FileUploadService();
        $user = auth()->user();
        DB::beginTransaction();
        try {
            if ($actionId = $this->storeAction($goalId, $krId, $actionData->body)) {
                foreach ($uploadedImages as $image) {
                    // Upload to S3 Bucket
                    $imageFileData = $fileUploadService->uploadToS3(FileUploadType::IMAGE_ACTION, $actionId, $image);
    
                    $imageFileData->userId = $user['id'];
                    $imageFileData->teamId = $user['current_team_id'];
                    $imageFileData->fileType = AttachedFileType::TYPE_MEDIA;
    
                    // Store an image data to 'attached_files' table: get new attached file id
                    $imageFileId = $fileUploadService->storeAttachedFiles($imageFileData);
    
                    // Store an image to action files
                    $this->storeActionFiles($imageFileData->teamId, $actionId, $imageFileId);
    
                }
                // Updage Goal and Key-result progress data.
                $this->updateGoalAndKrProgress($goalId, $krId, $actionData->krCurrentValue, $actionData->krNewValue);
            } else return false;

            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            throw new Exception($ex);
        }
        
        return true;
    }

    public function updateGoalAndKrProgress(int $goalId, int $krId, float $currentKrValue, float $newKrValue)
    {
        $krService = new KrsService();
        $goalService = new GoalsService();

        $currentKr = $krService->getKrByID($krId);
        
        $currentKr['current_value'] = $currentKrValue + $newKrValue;
        $krProgress = ($currentKr['current_value'] * 100) / $currentKr['target_value'];

        // Save new progress of Key-result.
        $krService->updateKrProgress($krId, $currentKr['current_value'], $krProgress);
        
        $totalPercentage = 0;
        $krs = $krService->getAllKrsInGoal($goalId);
        foreach ($krs as $kr) {
            $totalPercentage = $totalPercentage + $kr['progress'];
        }
        $goalProgress = round($totalPercentage / sizeof($krs), 2);

        // Save new progress of Goal.
        $goalService->updateGoalProgress($goalId, $goalProgress);
        
        return true;
    }

    // Store an Action
    public function storeAction(int $goalId, int $krId, string $body): int
    {
        $user = auth()->user();

        $newAction = new ActionPosts([
            'user_id' => $user['id'],
            'team_id' => $user['current_team_id'],
            'goal_id' => $goalId,
            'kr_id' => $krId,
            'body' => $body,
        ]);
        $newAction->save();

        return $newAction->id;
    }

    // Store action files
    public function storeActionFiles(int $teamId, int $actionId, int $attachedFileId)
    {
        $newFile = new ActionFiles([
            'team_id' => $teamId,
            'action_id' => $actionId,
            'attached_file_id' => $attachedFileId,
        ]);

        return $newFile->save();
    }

    // Update Action
    public function updateAction(int $id, string $body)
    {
        return true;
    }

    // Check Action exists or not
    public function actionExists(int $id): bool
    {
        return true;
    }

    // SoftDelete an Action
    public function softDeleteAction(int $id)
    {
        $acion = ActionPosts::where('id', $id)->first();
        if (empty($acion)) {
            return false;
        }

        $acion['del_flg'] = true;
        $acion['deleted_at'] = now();

        return $acion->save();
    }

    // Like Action Post
    public function likeActionPost(int $postId, int $userId, string $status, int $likeId)
    {
        if ($status === 'like') {
            // LIKE

            $like = Like::where('user_id', $userId)->where('likeable_id', $postId)->first();

            if (empty($like)) {
                return $this->createNewPostLike($postId, $userId);
            } else {
                $like['del_flg'] = false;
                $like['deleted_at'] = null;

                return $like->save();
            }
        } else {
            // DISLIKE
            if ($likeId === -1) {
                return false;
            }

            $like = Like::where('id', $likeId)->first();
            $like['del_flg'] = true;
            $like['deleted_at'] = now();

            return $like->save();
        }

        return true;
    }

    // Create New Like
    public function createNewPostLike(int $postId, int $userId)
    {
        $newLikable = new Like([
            'user_id' => $userId,
            'likeable_id' => $postId,
            'likeable_type' => LikableType::POST_LIKE,
        ]);
        $newLikable->save();

        return true;
    }

    // HardDelete an Action
    public function hardDeleteAction(int $id)
    {
        return ActionPosts::where('id', $id)->delete();
    }

}