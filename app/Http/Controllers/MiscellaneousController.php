<?php

namespace App\Http\Controllers;

use App\Models\SystemInterface;
use App\Models\DataExport;
use App\Models\PmsServiceRequest;
use App\Http\Requests\StoreSystemInterfaceRequest;
use App\Http\Requests\StoreDataExportRequest;
use App\Http\Requests\StoreServiceRequestRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MiscellaneousController extends Controller
{
    public function index(Request $request) {
        $tab = $request->get('tab', 'interfaces');
        $search = $request->get('search', '');

        $data = ['tab' => $tab, 'filters' => ['search' => $search]];

        if ($tab === 'interfaces') {
            $data['interfaces'] = SystemInterface::search($search)->latest()->paginate(12)->withQueryString();
            $data['metrics'] = [
                'total' => SystemInterface::count(),
                'active' => SystemInterface::where('status', 'connected')->count()
            ];
        } elseif ($tab === 'exports') {
            $data['exports'] = DataExport::search($search)->latest()->paginate(15)->withQueryString();
            $data['metrics'] = [
                'total' => DataExport::count(),
                'recent' => DataExport::where('created_at', '>=', now()->subDay())->count()
            ];
        } elseif ($tab === 'requests') {
            $data['requests'] = PmsServiceRequest::search($search)->latest()->paginate(15)->withQueryString();
            $data['metrics'] = [
                'open' => PmsServiceRequest::whereIn('status', ['open', 'in_progress'])->count(),
                'critical' => PmsServiceRequest::where('priority', 'critical')->where('status', '!=', 'closed')->count()
            ];
        }

        return Inertia::render('Miscellaneous/Index', $data);
    }

    // --- Interfaces CRUD ---
    public function storeInterface(StoreSystemInterfaceRequest $request) {
        SystemInterface::create($request->validated());
        return redirect()->back()->with('success', 'Interface added successfully.');
    }
    public function updateInterface(StoreSystemInterfaceRequest $request, SystemInterface $interface) {
        $interface->update($request->validated());
        return redirect()->back()->with('success', 'Interface updated.');
    }
    public function destroyInterface(SystemInterface $interface) {
        $interface->delete();
        return redirect()->back()->with('success', 'Interface removed.');
    }

    // --- Exports CRUD ---
    public function storeExport(StoreDataExportRequest $request) {
        $data = $request->validated();
        $data['status'] = 'completed'; // Simulated processing
        $data['requested_by'] = auth()->user()->name ?? 'System User';
        $data['file_path'] = '/storage/exports/' . Str::slug($data['name']) . '_' . time() . '.' . $data['format'];
        $data['file_size_kb'] = rand(1024, 15000);
        $data['expires_at'] = now()->addDays(7);
        
        DataExport::create($data);
        return redirect()->back()->with('success', 'Export generated successfully.');
    }
    public function destroyExport(DataExport $export) {
        $export->delete();
        return redirect()->back()->with('success', 'Export deleted.');
    }

    // --- Service Requests CRUD ---
    public function storeServiceRequest(StoreServiceRequestRequest $request) {
        $data = $request->validated();
        $data['ticket_number'] = 'TKT-' . strtoupper(Str::random(6));
        $data['reported_by'] = auth()->user()->name ?? 'System User';
        
        PmsServiceRequest::create($data);
        return redirect()->back()->with('success', 'Service Request submitted.');
    }
    public function updateServiceRequest(StoreServiceRequestRequest $request, PmsServiceRequest $pmsServiceRequest) {
        $pmsServiceRequest->update($request->validated());
        return redirect()->back()->with('success', 'Service Request updated.');
    }
    public function destroyServiceRequest(PmsServiceRequest $pmsServiceRequest) {
        $pmsServiceRequest->delete();
        return redirect()->back()->with('success', 'Service Request closed/deleted.');
    }
}
