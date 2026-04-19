<?php
namespace SureLab\Customers\Http\Controllers;
use App\User;
use App\Country;
use App\Customer;
use App\Highlight;
use App\Reservation;
use App\Handlers\Settings;
use Illuminate\Http\Request;
use Laravelista\Comments\Comment;
use App\Services\CustomPagination;
use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Http\Resources\HighlightResource;
use App\Http\Resources\ReservationManagement\ReservationResource;

class CustomerController extends Controller
{

    public function index(Request $request)
    {
        $customer = Customer::where('team_id' , auth()->user()->current_team_id)
                        ->with(['highlight', 'nationality','reservations'])
                        ->when($request->get('name') != null  , function($q) use($request){
                            $q->where('name', 'like', '%' . $request->get('name') . '%');
                        })
                        ->when($request->get('id_number') != null  , function($q) use($request){
                            $q->where('id_number', 'like', '%' . $request->get('id_number') . '%');
                        })
                        ->when($request->get('phone') != null  , function($q) use($request){
                            $q->where('phone', 'like', '%' . $request->get('phone') . '%');
                        })
                        ->when($request->get('highlight_id') != null  , function($q) use($request){
                            $q->where('highlight_id', $request->get('highlight_id'));
                        })
                        ->when($request->get('gender') != null  , function($q) use($request){
                            $q->where('gender', $request->get('gender'));
                        })
                        ->orderByDesc('id')
                        ->paginate(20);
        return $customer;
    }

    public function store(Request $request)
    {
         if(Customer::create($request->all())){
             return response()->json(['success' => true , 'message' => 'Customer Created Successfully']);
         }
         return response()->json(['success' => false , 'message' => 'Something went wrong']);
    }

    public function update($id, Request $request)
    {
        if(Customer::find($id)->update($request->customer)){
            return response()->json(['success' => true , 'message' => 'Customer Updated Successfully']);
        }
        return response()->json(['success' => false , 'message' => 'Something went wrong']);
    }

    public function show($id)
    {
        return Customer::find($id);
    }


    public function destroy($id)
    {
        if(Customer::destroy($id)){
            return response()->json(['success' => true , 'message' => 'Customer Deleted Successfully']);
        }
        return response()->json(['success' => false , 'message' => 'Something went wrong']);
    }

    public function filters(Request $request)
    {
        $customer = Customer::query()->where('team_id' , auth()->user()->current_team_id);
        if ($request["customer_name"]) {
            $res =  $customer->where('name', 'like', '%' . $request["customer_name"] . '%');
        }
        if ($request["idNumber"]) {
            $res = $customer->where('id_number', $request["idNumber"]);
        }
        if ($request["customer_type"]) {
            $res = $customer->where('customer_type', $request["customer_type"]);
        }
        if ($request["phoneNumber"]) {
            $res =  $customer->where('phone', $request["phoneNumber"]);
        }
        if ($request["gender"]) {
            $res =  $customer->where('gender', $request["gender"]);
        }

        $res = $customer->with(['highlight', 'nationality', 'reservations'])->paginate(5);
        return $res;
    }

    public function getUtilities()
    {
        return [
            'SCTH' => Settings::checkIntegration('SCTH', auth()->user()->current_team_id),
            'SHMS' => Settings::checkIntegration('SHMS', auth()->user()->current_team_id),
            'purpose_of_visit' => Customer::purposeOfVisit(),
            'nationalities' => CountryResource::collection(Country::all()),
            'id_types' => Customer::idTypes(),
            'customer_types' => Customer::customerTypes(),
        ];
    }

    /**
     * Get customer reservations
     * @note :  i could have eager load them in the show method
     * but as we doing a custom pagination inside the show i separated it
     * @param Request $request
     * @return void
     */
    public function getCustomerReservations(Request $request)
    {

        $reservtions = Reservation::with('unit')
        ->where('customer_id' , $request->customer_id)
        ->where('team_id' , auth()->user()->current_team_id)
        ->whereNull('deleted_at')
        // ->whereNull('company_id')
        ->orderByDesc('id')
        ->paginate(3);
        return ReservationResource::collection($reservtions);
    }


    /**
     * @description Fetch Customer Notes
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomerNotes(Request $request){
        $customerNotes = Comment::where('commentable_id' , $request->get('id'))->where('commentable_type' , 'App\\Customer')
            ->whereHasMorph('commenter' , [User::class] , function($u)  {
                $u->where('current_team_id' , auth()->user()->current_team_id);
            })
            ->orderByDesc('id')
            ->get();
        return response()->json((new CustomPagination($customerNotes))->paginate(4));
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
        if($comment->save()){
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }


    public function updateCustomerNote(Request $request , $id)
    {
        $comment = Comment::find($id);
        $comment->comment = preg_replace('~^\h+|\h+$|(\R){2,}|(\s){2,}~m', '$1$2', $request->get('note'));

        if($comment->save()){
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);

    }

    public function deleteCustomerNote($id)
    {
        $comment = Comment::find($id);

        if($comment->delete()){
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }

}
