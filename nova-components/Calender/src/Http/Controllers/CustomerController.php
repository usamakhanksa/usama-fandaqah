<?php

namespace SureLab\Calender\Http\Controllers;

use App\User;
use App\Customer;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Laravelista\Comments\Comment;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;

class CustomerController extends Controller
{
    public function filter(Request $request, Customer $customer)
    {
        if ($request->has('q') && $request->get('q')) {
            $search_string = $request->get('q');
            switch ($request->get('search_attribute')) {

                case 'by_id_number':
                    if (auth()->user()->current_team_id == 44) {
                        $customers = collect($customer->where('team_id', 44)->with('highlight')->searchByIDNumber($search_string)->get()->take(30))->unique('id_number');
                    } else {
                        $customers = collect($customer->with('highlight')->searchByIDNumber($search_string)->get()->take(30))->unique('id_number');
                    }
                    break;
                case 'by_email':
                    if (auth()->user()->current_team_id == 44) {
                        $customers = collect($customer->where('team_id', 44)->with('highlight')->searchByEmail($search_string)->get()->take(30))->unique('email');
                    } else {
                        $customers = collect($customer->with('highlight')->searchByEmail($search_string)->get()->take(30))->unique('email');
                    }
                    break;
                case 'by_phone':
                    if (auth()->user()->current_team_id == 44) {
                        $customers = collect($customer->where('team_id', 44)->with('highlight')->searchByPhone($search_string)->get()->take(30))->unique('phone');
                    } else {
                        $customers = collect($customer->with('highlight')->searchByPhone($search_string)->get()->take(30))->unique('phone');
                    }

                    break;
                default:
                    if (auth()->user()->current_team_id == 44) {
                        $customers = collect($customer->where('team_id', 44)->with('highlight')->searchByName($search_string)->get()->take(30))->unique('name');
                    } else {
                        $customers = collect($customer->with('highlight')->searchByName($search_string)->get()->take(30))->unique('name');
                    }
                    break;
            }
            /**
             * Dev tip : i made new instance of EloquentCollection cause collect by default comes from supportCollection
             */
            $eloquentCustomersCollection = new \Illuminate\Database\Eloquent\Collection;
            $eloquentCustomersCollection = $eloquentCustomersCollection->merge($customers);

            return response()->json($eloquentCustomersCollection);
        }

        return response()->json([]);
    }

    public function customersCount(NovaRequest $request)
    {
        $query = Customer::query();

        $filters = json_decode(base64_decode(\request('filters')), true);
        collect($filters)->each(function ($filter) use ($request, $query) {
            if (!is_null($filter['value']) and !empty($filter['value'])) {
                (new $filter['class'])->apply($request, $query, $filter['value']);
            }
        });

        $query = \App\Nova\Customer::indexQuery($request, $query);

        $customers = $query->get()->pluck('id')->count();

        return $customers;
    }

    public function getCustomerNotes(Request $request)
    {

        $customerNotes = Comment::where('commentable_id', $request->get('id'))->where('commentable_type', 'App\\Customer')
            ->whereHasMorph('commenter', [User::class], function ($u) {
                $u->where('current_team_id', auth()->user()->current_team_id);
            })
            ->get();
        return response()->json($customerNotes);
    }

    public function getCustomerInfo(Request $request, Customer $customer)
    {

        return response()->json($customer);
    }
}
