<?php
namespace App\Policies;
use App\Models\{User,Comment};
class CommentPolicy {
    public function delete(User $user, Comment $record): bool { return $user->isAdmin() || $user->id===$record->user_id; }
}
