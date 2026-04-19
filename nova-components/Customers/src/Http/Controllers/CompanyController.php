<?php

namespace SureLab\Customers\Http\Controllers;

use App\Company;
use App\Customer;
use App\CompanyNote;
use App\Reservation;
use Illuminate\Http\Request;
use App\Services\ExcelService;
use App\Services\PrintService;
use App\Services\CustomPagination;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Resources\CompanyResource;
use App\Http\Requests\AddCompanyRequest;
use App\Http\Requests\AddIndividualRequest;
use App\Http\Requests\UpdateCompanyRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\ReservationManagement\ReservationResource;

class CompanyController extends Controller
{
    /**
     * List companies
     *
     * @param Request $request
     * @return void
     */
    public function list(Request $request)
    {
        $companies = QueryBuilder::for(Company::class)
                                    ->where('team_id' , auth()->user()->current_team_id)
                                    ->whereNull('deleted_at')
                                    ->with(['creator'])
                                    ->allowedFilters([
                                        AllowedFilter::scope('by_company_name'),
                                        AllowedFilter::scope('by_company_phone'),
                                        AllowedFilter::scope('by_creator'),
                                        AllowedFilter::scope('by_company_type')
                                    ])
                                    ->orderByDesc('id')
                                    ->paginate(20);

        return CompanyResource::collection($companies);
    }

    /**
     * Add new company
     * @param AddCompanyRequest $request
     * @return void
     */
    public function store(AddCompanyRequest $request)
    {
        $company = Company::create([
            'team_id' => $request->get('team_id'),
            'user_id' => $request->get('user_id'),
            'name' => $request->get('name'),
            'phone' => str_replace(' ', '', $request->get('phone')),
            'city' => $request->get('city'),
            'address' => $request->get('address'),
            'person_incharge_name' => $request->get('person_incharge_name'),
            'person_incharge_phone' => $request->get('person_incharge_phone') ? preg_replace('/\s+/', '', $request->get('person_incharge_phone'))  : null,
            'email' => $request->get('email'),
            'tax_number' => $request->get('tax_number'),
        ]);

        if($request->has('reservation_id')){
            $reservation = Reservation::find($request->get('reservation_id'));
            if($request->get('reservation_type') == 'group'){
                // its a group reservation of entity type individual and needs a company 
                $push_main_reservation_to_collection = false;
                if(is_null($reservation->attachable_id)){
                    $main_reservation = $reservation;
                    $push_main_reservation_to_collection = false;
                }else{
                    $main_reservation = Reservation::find($reservation->attachable_id);
                    $push_main_reservation_to_collection = true;
                }
                $reservations = Reservation::with('wallet','unit')
                                ->where('reservation_type' , 'group')
                                ->where('company_id' , $reservation->company_id)
                                ->where(function ($query) use($reservation,$main_reservation) {
                                    return $query->with('unit')->where('id',$reservation->id)->orWhere('attachable_id',$main_reservation->id);
                                })
                                ->where('status' , 'confirmed')
                                ->whereNull('deleted_at')
                                ->get();

                if($push_main_reservation_to_collection){
                    $reservations->push($main_reservation);
                }

                if(count($reservations)){
                    foreach($reservations as $reservationObject){
                        $reservationObject->company_id = $company->id;
                        $reservationObject->save();
                    }

                    return response()->json(['success' => true , 'reload_reservation' => true ] , Response::HTTP_CREATED);
                }
            }else{
                // its a single reservation
                $reservation->company_id = $company->id;
                $reservation->reservation_type = 'group';
                $reservation->save();
                return response()->json(['success' => true , 'reload_reservation' => true ] , Response::HTTP_CREATED);
            }
            
        }
        return response()->json(['success' => true , 'company' => $company ] , Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company)
    {
        return response()->json($company);
    }

    /**
     * Update company
     * @param UpdateCompanyRequest $request
     * @param Company $company
     * @return void
     */
    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $company = Company::find($request->get('id'));
        try {
            $company->update([
                'id' => $request->get('id'),
                'name' => $request->get('name'),
                'phone' => $request->get('phone'),
                'city' => $request->get('city'),
                'address' => $request->get('address'),
                'person_incharge_name' => $request->get('person_incharge_name'),
                'person_incharge_phone' => $request->get('person_incharge_phone') ? preg_replace('/\s+/', '', $request->get('person_incharge_phone')) : null,
                'email' => $request->get('email'),
                'tax_number' => $request->get('tax_number'),
            ]);
            return response()->json('updated' , Response::HTTP_OK);

        } catch (\Exception $th) {
            return response()->json('failed' , Response::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    public function getCompanyNotes(Request $request, Company $company)
    {
        $notes = CompanyNote::with('creator')
                ->whereNull('deleted_at')
                ->where('team_id', auth()->user()->current_team_id)
                ->where('company_id' , $company->id)
                ->orderByDesc('id')
                ->get();
        return response()->json((new CustomPagination($notes))->paginate(4));
    }

    public function storeCompanyNote(Request $request,Company $company)
    {
        if(CompanyNote::create([
            'company_id' => $company->id,
            'team_id' => auth()->user()->current_team_id,
            'created_by' => auth()->user()->id,
            'body' => $request->note
        ])){
            return response()->json(['success' => true] , 201);
        }
        return response()->json(['success' => false] , 500);
    }

    public function updateCompanyNote(Request $request,  CompanyNote $companyNote)
    {

        $companyNote->body = $request->note;
        if($companyNote->save()){
            return response()->json(['success' => true] , 200);
        }
        return response()->json(['success' => false] , 500);
    }

    public function deleteCompanyNote(Request $request , CompanyNote $companyNote)
    {
        if($companyNote->delete()){
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }

    /**
     * Auto complete search for company
     *
     * @param Request $request
     * @param Company $company
     * @return void
     */
    public function search(Request $request)
    {
        if($request->has('entity')){
            $entity = $request->get('entity');
        }else{
            $entity = 'company';
        }
        $companies = Company::where('team_id' , auth()->user()->current_team_id)
        ->where('entity_type' , $entity)
        ->bySearch($request->get('q'))
        ->get();
        return response()->json($companies);
    }


    public function getCompanyReservations(Request $request, Company $company)
    {
        return response()->json((new CustomPagination(ReservationResource::collection($company->companyGroupReservations)))->paginate(5));
    }


    public function storeIndividual(AddIndividualRequest $request)
    {
        $company = Company::create([
            'team_id' => $request->get('team_id'),
            'user_id' => $request->get('user_id'),
            'name' => $request->get('name'),
            'phone' => str_replace(' ', '', $request->get('phone')),
            'entity_type' => 'individual'
        ]);

        if($request->has('reservation_id')){
            $reservation = Reservation::find($request->get('reservation_id'));
            if($request->get('reservation_type') == 'group'){
                // its a group reservation of entity type individual and needs a company 
                $push_main_reservation_to_collection = false;
                if(is_null($reservation->attachable_id)){
                    $main_reservation = $reservation;
                    $push_main_reservation_to_collection = false;
                }else{
                    $main_reservation = Reservation::find($reservation->attachable_id);
                    $push_main_reservation_to_collection = true;
                }
                $reservations = Reservation::with('wallet','unit')
                                ->where('reservation_type' , 'group')
                                ->where('company_id' , $reservation->company_id)
                                ->where(function ($query) use($reservation,$main_reservation) {
                                    return $query->with('unit')->where('id',$reservation->id)->orWhere('attachable_id',$main_reservation->id);
                                })
                                ->where('status' , 'confirmed')
                                ->whereNull('deleted_at')
                                ->get();

                if($push_main_reservation_to_collection){
                    $reservations->push($main_reservation);
                }

                if(count($reservations)){
                    foreach($reservations as $reservationObject){
                        $reservationObject->company_id = $company->id;
                        $reservationObject->save();
                    }

                    return response()->json(['success' => true , 'reload_reservation' => true ] , Response::HTTP_CREATED);
                }
            }else{
                // its a single reservation
                $reservation->company_id = $company->id;
                $reservation->reservation_type = 'group';
                $reservation->save();
                return response()->json(['success' => true , 'reload_reservation' => true ] , Response::HTTP_CREATED);
            }
            
        }
        return response()->json(['success' => true , 'company' => $company ] , Response::HTTP_CREATED);
    }

    public function storeIndividualFromCustomer(Request $request){
        
        $customer = $request->customer;
        if ($customer['id_number'] && !$customer['id']) {

            $validator = Customer::validate($customer, $customer['id']);

            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => 'id_number_taken'
                ];
                return response()->json($response);
            }

        }

        $customer['phone'] = preg_replace('/\s+/', '', $customer['phone']);

        $customer = Customer::updateOrCreate(['id' => $customer['id']], $customer);

        $company_blue_print = [
            'name' => $customer->name,
            'phone' => $customer->phone,
            'entity_type' => 'individual',
            'user_id' => auth()->user()->id,
            'customer_id' => $customer->id
        ];
        $updatedOrCreatedIndividualCompany = Company::updateOrCreate(['name' => $customer->name, 'phone' => $customer->phone , 'team_id' => auth()->user()->current_team_id], $company_blue_print);

        $response = [
            'success' => true,
            'message' => 'individual created successfully',
            'customer' => $customer,
            'company' => $updatedOrCreatedIndividualCompany
        ];
        return response()->json($response);
        
    }
}
