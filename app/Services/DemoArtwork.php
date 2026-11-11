<?php
namespace App\Services;
use Illuminate\Support\Facades\Storage;
class DemoArtwork {
    /** Original procedural demo artwork; transparent layers all use a 512px canvas. */
    public function layer(string $category,int $variant=0): string {
        $im=imagecreatetruecolor(512,512); imagealphablending($im,false);
        $clear=imagecolorallocatealpha($im,0,0,0,127); imagefill($im,0,0,$clear); imagesavealpha($im,true); imagealphablending($im,true);
        $skin=imagecolorallocate($im,244,198,166); $ink=imagecolorallocate($im,44,40,69);
        $palette=[[119,101,220],[62,160,160],[212,123,158],[205,153,73],[104,146,213],[104,176,124]];
        $rgb=$palette[$variant%count($palette)]; $color=imagecolorallocate($im,...$rgb);
        switch($category) {
            case 'background':
                $bg=imagecolorallocate($im,237,233,248); imagefilledrectangle($im,0,0,511,511,$bg);
                $glow=imagecolorallocate($im,250,247,255); imagefilledellipse($im,256,226,380,380,$glow);
                imagefilledellipse($im,256,468,270,30,imagecolorallocate($im,216,208,232)); break;
            case 'body':
                imagefilledellipse($im,256,179,166,200,$skin);
                imagefilledrectangle($im,227,248,285,313,$skin);
                imagefilledellipse($im,256,378,212,186,$skin);
                imagefilledellipse($im,173,370,52,144,$skin); imagefilledellipse($im,339,370,52,144,$skin); break;
            case 'face':
                imagefilledellipse($im,224,181,13,20,$ink); imagefilledellipse($im,288,181,13,20,$ink);
                imagesetthickness($im,4); imagearc($im,256,215,47,30,10,170,$ink);
                $blush=imagecolorallocatealpha($im,226,123,142,55); imagefilledellipse($im,208,208,29,14,$blush); imagefilledellipse($im,304,208,29,14,$blush); break;
            case 'hair':
                $hair=imagecolorallocate($im,65+($variant%3)*20,45,79+($variant%3)*18);
                imagefilledarc($im,256,155,188,192,180,360,$hair,IMG_ARC_PIE);
                imagefilledellipse($im,181,188,35,141,$hair); imagefilledellipse($im,331,188,35,141,$hair);
                imagefilledellipse($im,221,113,100,63,$hair); break;
            case 'clothes':
                imagefilledpolygon($im,[204,298,227,290,256,313,285,290,308,298,349,357,321,380,311,348,322,456,190,456,201,348,191,380,163,357],$color);
                $trim=imagecolorallocate($im,255,244,222); imagefilledellipse($im,256,336,14,14,$trim); imagefilledellipse($im,256,368,14,14,$trim); break;
            case 'accessory':
                imagesetthickness($im,5); imageellipse($im,224,182,47,37,$color); imageellipse($im,288,182,47,37,$color); imageline($im,247,180,265,180,$color); break;
        }
        ob_start(); imagepng($im); $png=ob_get_clean(); imagedestroy($im);
        $path='demo/'.$variant.'-'.$category.'.png'; Storage::disk('local')->put($path,$png); return $path;
    }
    public function composite(array $paths,string $name): string {
        $im=imagecreatetruecolor(512,512); imagealphablending($im,false); imagefill($im,0,0,imagecolorallocatealpha($im,0,0,0,127)); imagesavealpha($im,true); imagealphablending($im,true);
        foreach($paths as $path) { $layer=imagecreatefrompng(Storage::disk('local')->path($path)); imagecopy($im,$layer,0,0,0,0,512,512); imagedestroy($layer); }
        ob_start(); imagepng($im); $png=ob_get_clean(); imagedestroy($im); $path='demo/'.$name.'.png'; Storage::disk('local')->put($path,$png); return $path;
    }
}
