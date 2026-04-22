<?php
namespace App\Http\Controllers\Foundation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Foundation\StoreReservationRequest;
use App\Http\Requests\Foundation\UpdateReservationRequest;
use App\Models\Foundation\Customer;
use App\Models\Foundation\Reservation;
use App\Models\Foundation\Unit;
use App\Services\Foundation\ReservationService;
use Inertia\Inertia;

class ReservationController extends Controller
{
    public function __construct(private readonly ReservationService $service) {}

    public function index(){ return Inertia::render('Foundation/Reservations/Index',['rows'=>Reservation::with(['customer','unit'])->latest()->paginate(10)]); }
    public function create(){ return Inertia::render('Foundation/Reservations/Create',['customers'=>Customer::select('id','first_name_en','last_name_en')->get(),'units'=>Unit::select('id','number','name')->get()]); }
    public function store(StoreReservationRequest $request){ $this->service->create($request->validated()); return to_route('foundation.reservations.index')->with('success','Reservation created.'); }
    public function show(Reservation $reservation){ return Inertia::render('Foundation/Reservations/Show',['row'=>$reservation->load(['customer','unit'])]); }
    public function edit(Reservation $reservation){ return Inertia::render('Foundation/Reservations/Edit',['row'=>$reservation,'customers'=>Customer::select('id','first_name_en','last_name_en')->get(),'units'=>Unit::select('id','number','name')->get()]); }
    public function update(UpdateReservationRequest $request, Reservation $reservation){ $this->service->update($reservation, $request->validated()); return to_route('foundation.reservations.index')->with('success','Reservation updated.'); }
    public function destroy(Reservation $reservation){ $reservation->delete(); return to_route('foundation.reservations.index')->with('success','Reservation deleted.'); }
}
