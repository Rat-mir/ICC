<?php
namespace App\Policies;
use App\Models\{User,Character};
class CharacterPolicy {
    public function view(User $user, Character $record): bool { return $user->isAdmin() || $user->id===$record->user_id; }
    public function update(User $user, Character $record): bool { return $this->view($user,$record); }
    public function delete(User $user, Character $record): bool { return $this->view($user,$record); }
}
