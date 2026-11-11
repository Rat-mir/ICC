<?php
namespace App\Services;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class ImageService {
    /** Decode and re-encode PNG to remove embedded data; FormRequest limits size and dimensions. */
    public function store(UploadedFile $file,string $folder): string {
        $image=@imagecreatefrompng($file->getRealPath());
        if(!$image) throw ValidationException::withMessages(['image'=>['PNG повреждён.']]);
        imagesavealpha($image,true); ob_start(); imagepng($image); $png=ob_get_clean(); imagedestroy($image);
        $path=$folder.'/'.Str::uuid().'.png';
        if(!Storage::disk('local')->put($path,$png)) throw new \RuntimeException('Не удалось сохранить PNG.');
        return $path;
    }
    public function dimensions(string $path): array {
        $size=@getimagesize(Storage::disk('local')->path($path));
        if(!$size || $size[2]!==IMAGETYPE_PNG) throw ValidationException::withMessages(['assets'=>['PNG отсутствует или повреждён.']]);
        return [$size[0],$size[1]];
    }
    public function response(string $path) {
        abort_unless(Storage::disk('local')->exists($path),404);
        return response(Storage::disk('local')->get($path),200,['Content-Type'=>'image/png','X-Content-Type-Options'=>'nosniff','Cache-Control'=>'private, no-store']);
    }
}
