<?php

namespace App\Http\Controllers;

use App\Note;
use App\User;
use App\NoteDay;
use Carbon\Carbon;
use App\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\NoteResource;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Exceptions\ValidationException;
use App\Http\Resources\NoteDayResource;
use Illuminate\Support\Facades\Validator;

class NoteController extends Controller
{
	/**
	* get note index method
    *
	* @return App\Http\Resources\NoteDayResource
	*/
	public function index(Request $request)
	{

        $filter_by_date = $request->get('filter_by_date');
        $filter_by_user = $request->get('filter_by_user') ?? null;
        $days = NoteDay::where('team_id',auth()->user()->current_team_id)
                    // ->whereHas('notes')
                    // ->with('notes')
                    ->with([
                        'notes' => function($query) use ($filter_by_user){
                            if($filter_by_user != null){
                                $query->where('created_by_id', $filter_by_user);
                            }
                          }
                    ])
                    ->when($filter_by_date != 'null' , function ($query) use($filter_by_date) {
                        $query->where('day', 'LIKE' , '%' . $filter_by_date . '%');
                    })
                    ->orderBy('day','desc')
                    ->paginate(3);
        return NoteDayResource::collection($days);

	}

    /**
    * store new note  method
    *
    * @return App\Http\Resources\NoteDayResource
    */
    public function store(Request $request)
    {

        if(!$request->get('body')){
            return response()->json(['status' => 'body_required']);
        }

        $day =  NoteDay::updateOrCreate(['day' => Carbon::now()->startOfDay()]);
        $note = new Note;
        $note->body = $request->body;
        $note->day_id = $day->id;
        $note->created_by_id = auth()->user()->id;
        $note->team_id = auth()->user()->current_team_id;
        $note->save();

        return  new NoteResource($note);
    }

    /**
    * update new note  method
    *
    * @return App\Http\Resources\NoteDayResource
    */
    public function update(Request $request, Note $note)
    {

        if(!$request->get('body')){
            return response()->json(['status' => 'body_required']);
        }

        $note->body = $request->body;
        $note->save();

        return new NoteResource($note);
    }

    /**
    * delete new note  method
    *
    * @return App\Http\Resources\NoteDayResource
    */
    public function delete(Request $request,Note $note)
    {
        $note->delete();
        return response()->json(['status' => true]);
    }

    public function userIsAdmin(Request $request)
    {
        $user = User::find($request->id);
        $users = User::where('current_team_id' , auth()->user()->current_team_id)->whereNull('deleted_at')->get();
        return response()->json(['is_admin' => $user->isAdmin() , 'users' => $users]);
    }
}
