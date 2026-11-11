<?php
use Illuminate\Support\Facades\Route;
Route::get('/',fn()=>response()->json(['app'=>'ICC','api'=>'/api/health','client'=>env('CLIENT_ORIGINS')]));
