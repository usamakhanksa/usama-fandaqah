<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{Guest,Room};
use Illuminate\Http\Request;
class SearchController extends Controller {
  public function autocomplete(Request $request){
    $term=$request->string('q');
    return response()->json([
      'guests'=>Guest::where('name','like',"%{$term}%")->limit(5)->get(['id','name']),
      'rooms'=>Room::where('number','like',"%{$term}%")->limit(5)->get(['id','number'])
    ]);
  }
}
