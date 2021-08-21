<?php

declare(strict_types=1);

namespace App\Enums\Likes;

abstract class LikableType
{
    const POST_LIKE = 'App\ActionPosts';
    const COMMENT_LIKE = 'App\Comments';
    // const MESSAGE_LIKE = 2;
}
