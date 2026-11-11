<?php
namespace App\Services;
use App\Enums\MakerStatus;
use App\Exceptions\BusinessException;
use App\Models\{User,Maker,Asset};
use Illuminate\Support\Facades\{DB,Log,Storage};
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
class MakerService {
    public function __construct(private ImageService $images) {}
    public function list(array $f,?User $user,bool $admin=false) {
        $q=Maker::with('creator')->withCount(['assets'=>fn($q)=>$q->where('is_active',true),'characters']);
        if($admin) { /* Explicit admin route only. */ }
        elseif(!empty($f['mine']) && $user) $q->where('user_id',$user->id);
        else $q->where('status',MakerStatus::Approved->value);
        return $q->search($f['search']??null)
            ->when($f['status']??null,fn($q,$v)=>$q->where('status',$v))
            ->when($f['category']??null,fn($q,$v)=>$q->whereHas('assets',fn($q)=>$q->where('category',$v)->where('is_active',true)))
            ->when($f['from']??null,fn($q,$v)=>$q->whereDate('created_at','>=',$v))
            ->when($f['to']??null,fn($q,$v)=>$q->whereDate('created_at','<=',$v))
            ->orderBy($f['sort']??'created_at',$f['order']??'desc')->orderBy('id')
            ->paginate($f['per_page']??20)->withQueryString();
    }
    public function detail(Maker $maker,?User $user): Maker {
        $private=$user && ($user->isAdmin() || $user->id===$maker->user_id);
        return $maker->load(['creator','assets'=>fn($q)=>$q->when(!$private,fn($q)=>$q->where('is_active',true))])->loadCount('characters');
    }
    public function create(User $user,array $data): Maker { return $user->makers()->create($data); }
    public function update(Maker $maker,array $data): Maker {
        return DB::transaction(function()use($maker,$data) {
            $maker=Maker::lockForUpdate()->findOrFail($maker->id); $this->editable($maker);
            $maker->fill($data)->save(); return $maker;
        });
    }
    private function editable(Maker $maker): void {
        if(!$maker->status->canEdit()) throw new BusinessException('Набор можно менять только в черновике, после возврата или снятия с публикации для повторной проверки.');
        if($maker->status===MakerStatus::Approved) {
            $maker->status=MakerStatus::Draft; $maker->reviewed_by=null; $maker->reviewed_at=null; $maker->review_comment=null; $maker->save();
        }
    }
    public function delete(Maker $maker): void {
        $paths=DB::transaction(function()use($maker) {
            $maker=Maker::lockForUpdate()->findOrFail($maker->id);
            if(!in_array($maker->status,[MakerStatus::Draft,MakerStatus::Revision],true)) throw new BusinessException('Удалить можно только черновик или возвращённый набор.');
            if($maker->characters()->exists()) throw new BusinessException('Набор используется сохранёнными персонажами.');
            $paths=$maker->assets()->pluck('file_path')->all(); $maker->delete(); return $paths;
        });
        Storage::disk('local')->delete($paths);
    }
    public function asset(Maker $maker,array $data,?Asset $asset=null): Asset {
        $path=isset($data['image'])?$this->images->store($data['image'],'assets'):null;
        try {
            $saved=DB::transaction(function()use($maker,$data,$asset,$path) {
                $maker=Maker::lockForUpdate()->findOrFail($maker->id); $this->editable($maker);
                if($asset) { $asset=Asset::lockForUpdate()->findOrFail($asset->id); abort_unless($asset->maker_id===$maker->id,404); }
                else { $asset=new Asset; $asset->maker()->associate($maker); }
                $asset->fill(Arr::except($data,['image'])); if($path) $asset->file_path=$path; $asset->save(); return $asset;
            });
        } catch(\Throwable $e) { if($path) Storage::disk('local')->delete($path); throw $e; }
        return $saved;
    }
    public function deleteAsset(Maker $maker,Asset $asset): void {
        $path=DB::transaction(function()use($maker,$asset) {
            $maker=Maker::lockForUpdate()->findOrFail($maker->id); $this->editable($maker);
            $asset=Asset::lockForUpdate()->findOrFail($asset->id); abort_unless($asset->maker_id===$maker->id,404);
            if($asset->characters()->exists()) throw new BusinessException('Элемент используется сохранённым персонажем.');
            $path=$asset->file_path; $asset->delete(); return $path;
        });
        Storage::disk('local')->delete($path);
    }
    private function complete(Maker $maker): void {
        $assets=$maker->assets()->where('is_active',true)->get();
        if($assets->isEmpty() || !$assets->contains('category','body') || !$assets->contains('category','face'))
            throw ValidationException::withMessages(['assets'=>['Нужны активные слои body и face.']]);
        $size=null;
        foreach($assets as $asset) {
            $dimensions=$this->images->dimensions($asset->file_path);
            if($size && $size!==$dimensions) throw ValidationException::withMessages(['assets'=>['Все PNG набора должны иметь одинаковые размеры.']]);
            $size=$dimensions;
        }
    }
    public function submit(Maker $maker): Maker {
        return DB::transaction(function()use($maker) {
            $maker=Maker::lockForUpdate()->findOrFail($maker->id);
            if(!$maker->status->canSubmit()) throw new BusinessException('Отправка разрешена только из draft или revision.');
            $this->complete($maker); $maker->status=MakerStatus::Pending; $maker->submitted_at=now();
            $maker->reviewed_by=null; $maker->reviewed_at=null; $maker->review_comment=null; $maker->save();
            Log::info('maker.submitted',['maker_id'=>$maker->id,'user_id'=>$maker->user_id]); return $maker;
        });
    }
    public function review(Maker $maker,User $admin,array $data): Maker {
        return DB::transaction(function()use($maker,$admin,$data) {
            $maker=Maker::lockForUpdate()->findOrFail($maker->id);
            if($maker->status!==MakerStatus::Pending) { Log::warning('maker.invalid_review',['maker_id'=>$maker->id]); throw new BusinessException('Рассматривать можно только заявку pending.'); }
            if($data['decision']==='approve') $this->complete($maker);
            $maker->status=match($data['decision']) {'approve'=>MakerStatus::Approved,'reject'=>MakerStatus::Rejected,'request-revision'=>MakerStatus::Revision};
            $maker->reviewed_by=$admin->id; $maker->reviewed_at=now(); $maker->review_comment=$data['review_comment']??null; $maker->save();
            Log::info('maker.reviewed',['maker_id'=>$maker->id,'admin_id'=>$admin->id,'decision'=>$data['decision']]); return $maker;
        });
    }
    public function hide(Maker $maker,User $admin): Maker {
        return DB::transaction(function()use($maker,$admin) {
            $maker=Maker::lockForUpdate()->findOrFail($maker->id);
            if($maker->status!==MakerStatus::Approved) throw new BusinessException('Скрыть можно только опубликованный мейкер.');
            $maker->status=MakerStatus::Hidden; $maker->reviewed_by=$admin->id; $maker->reviewed_at=now(); $maker->save(); return $maker;
        });
    }
    public function statistics(): array {
        return ['makers_by_status'=>Maker::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total','status'),
            'users'=>User::count(),'posts'=>\App\Models\Post::count(),'characters'=>\App\Models\Character::count()];
    }
}
