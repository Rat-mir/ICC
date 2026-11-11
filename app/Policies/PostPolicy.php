<?php
namespace App\Policies;
use App\Models\{User,Post};
class PostPolicy {
    public function view(User $user, Post $record): bool { return $user->isAdmin() || $user->id===$record->user_id; }
    public function update(User $user, Post $record): bool { return $this->view($user,$record); }
    public function delete(User $user, Post $record): bool { return $this->view($user,$record); }
}
