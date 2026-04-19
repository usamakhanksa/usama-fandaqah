<?php

namespace Surelab\CustomerNotes\Http\Controllers;

use App\User;
use App\Customer;
use Illuminate\Http\Request;
use Laravelista\Comments\Comment;
use App\Http\Controllers\Controller;

class ToolController extends Controller
{

    /**
     * @description Fetch Customer Notes
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function customerNotes(Request $request){

        $customerNotes = Comment::where('commentable_id' , $request->get('id'))->where('commentable_type' , 'App\\Customer')
                    ->whereHasMorph('commenter' , [User::class] , function($u)  {
                        $u->where('current_team_id' , auth()->user()->current_team_id);
                    })
                    ->get();
        // $customerNotes = Customer::find($request->get('id'))->comments;
        return response()->json($customerNotes);
    }

    /**
     * @description  Store Note on specific customer
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeCustomerNote(Request $request){

        $model = $request->get('commentableType')::findOrFail($request->get('commentableId'));
        $commentClass = config('comments.model');
        $comment = new $commentClass;
        $comment->commenter()->associate(auth()->user());
        $comment->commentable()->associate($model);
        $comment->comment = preg_replace('~^\h+|\h+$|(\R){2,}|(\s){2,}~m', '$1$2', $request->get('note'));
        $comment->approved = !config('comments.approval_required');
        $comment->save();
        return response()->json(['status' => 'note_created'] , 201);


    }

}
