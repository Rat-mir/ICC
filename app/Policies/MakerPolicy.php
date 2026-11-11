<?php
namespace App\Policies;
use App\Models\{User,Maker};
use App\Enums\{MakerStatus,UserRole};
class MakerPolicy {
    public function view(?User $user, Maker $maker): bool { return $maker->status===MakerStatus::Approved || ($user && !$user->is_blocked && ($user->isAdmin() || $user->id===$maker->user_id)); }
    public function create(User $user): bool { return in_array($user->role,[UserRole::Creator,UserRole::Admin],true); }
    public function update(User $user, Maker $maker): bool { return $this->create($user) && ($user->isAdmin() || $user->id===$maker->user_id); }
    public function delete(User $user, Maker $maker): bool { return $this->update($user,$maker); }
}
