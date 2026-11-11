<?php
namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;
class HealthController {
    public function __invoke() {
        try { DB::select('select 1'); $ok=true; } catch(\Throwable $e) { $ok=false; }
        return response()->json(['app'=>config('app.name'),'time'=>now()->toIso8601String(),'database'=>$ok?'ok':'unavailable'],$ok?200:503);
    }
}
