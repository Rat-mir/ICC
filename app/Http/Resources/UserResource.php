<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class UserResource extends JsonResource {
    public function toArray($r): array { return ['id'=>$this->id,'name'=>$this->name,'email'=>$this->email,'role'=>$this->role->value,'is_blocked'=>$this->is_blocked,'created_at'=>$this->created_at]; }
}
