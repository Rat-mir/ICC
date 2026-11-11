<?php
namespace App\Services;
use App\Enums\MakerStatus;
use App\Exceptions\BusinessException;
use App\Models\{User,Maker,Character};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class CharacterService {
    public function list(User $user,int $perPage) { return Character::with(['maker','assets'])->when(!$user->isAdmin(),fn($q)=>$q->where('user_id',$user->id))->latest()->paginate($perPage)->withQueryString(); }
    public function save(User $user,array $data,?Character $character=null): Character {
        return DB::transaction(function()use($user,$data,$character) {
            $maker=Maker::lockForUpdate()->findOrFail($data['maker_id']);
            if($maker->status!==MakerStatus::Approved) throw new BusinessException('Сохранение доступно только для одобренного мейкера.');
            $assets=$maker->assets()->where('is_active',true)->whereIn('id',$data['asset_ids'])->get();
            if($assets->count()!==count($data['asset_ids'])) throw ValidationException::withMessages(['asset_ids'=>['Все элементы должны быть активны и принадлежать одному мейкеру.']]);
            if($assets->pluck('category')->unique()->count()!==$assets->count()) throw ValidationException::withMessages(['asset_ids'=>['Выберите не более одного элемента каждой категории.']]);
            if($character) $character=Character::lockForUpdate()->findOrFail($character->id);
            else { $character=new Character; $character->user()->associate($user); }
            $character->name=$data['name']; $character->maker()->associate($maker); $character->save();
            $pivot=[]; foreach($data['asset_ids'] as $order=>$id) $pivot[$id]=['layer_order'=>$order];
            $character->assets()->sync($pivot);
            return $character->load(['maker','assets']);
        });
    }
    public function delete(Character $character): void { $character->delete(); }
}
