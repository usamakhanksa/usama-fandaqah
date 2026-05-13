<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\ReservationRepository;
use App\Services\ContractService;
use App\Models\ReservationContract;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ReservationContractController extends Controller
{
    protected ReservationRepository $repository;
    protected ContractService $contractService;

    public function __construct(ReservationRepository $repository, ContractService $contractService)
    {
        $this->repository = $repository;
        $this->contractService = $contractService;
    }

    /**
     * Display a listing of reservation contracts.
     */
    public function index(Request $request): JsonResponse
    {
        $team = $request->user()->currentTeam;
        if (!$team) {
            return response()->json(['data' => []]);
        }

        $filters = $request->only(['date', 'status', 'reservation_id', 'search', 'per_page']);
        $contracts = $this->repository->getContractHistory($team, $filters)
            ->paginate($filters['per_page'] ?? 15);

        return response()->json($contracts);
    }

    /**
     * Display the specified contract details.
     */
    public function show($id): JsonResponse
    {
        $contract = ReservationContract::with([
            'reservation.customer',
            'reservation.unit',
            'generatedBy',
            'signedBy'
        ])->findOrFail($id);

        return response()->json(['data' => $contract]);
    }

    /**
     * Generate a new contract for a reservation.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'status' => 'nullable|in:draft,pending',
        ]);

        $team = $request->user()->currentTeam;
        $reservation = Reservation::findOrFail($validated['reservation_id']);

        // Check if reservation belongs to team
        if ($reservation->team_id !== $team->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Generate contract using the existing service
        $status = $validated['status'] ?? 'draft';
        $contract = $this->contractService->makeContractSnapshot($reservation, $status);

        // Update with additional metadata
        $contract->update([
            'contract_number' => $this->repository->generateContractNumber($team),
            'generated_at' => now(),
            'generated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Contract generated successfully',
            'data' => $contract->load(['reservation.customer', 'generatedBy'])
        ], 201);
    }

    /**
     * Download contract PDF.
     */
    public function download($id): JsonResponse
    {
        $contract = ReservationContract::findOrFail($id);
        $team = auth()->user()->currentTeam;

        if ($contract->team_id !== $team->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$contract->html_path) {
            return response()->json(['message' => 'Contract file not found'], 404);
        }

        // Check if PDF exists, if not generate it
        if (!$contract->pdf_path) {
            $pdfPath = $this->generatePdf($contract);
            $contract->update(['pdf_path' => $pdfPath]);
        }

        $url = $contract->pdf_url ?? $contract->html_url;

        return response()->json([
            'data' => [
                'download_url' => $url,
                'contract_number' => $contract->contract_number,
            ]
        ]);
    }

    /**
     * Mark contract as signed.
     */
    public function sign(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'signature_data' => 'nullable|string',
        ]);

        $contract = ReservationContract::findOrFail($id);
        $team = auth()->user()->currentTeam;

        if ($contract->team_id !== $team->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $contract->markAsSigned();

        if (!empty($validated['signature_data'])) {
            $contract->update(['signature_data' => $validated['signature_data']]);
        }

        return response()->json([
            'message' => 'Contract marked as signed',
            'data' => $contract->load(['reservation.customer', 'signedBy'])
        ]);
    }

    /**
     * Generate PDF from HTML contract.
     */
    protected function generatePdf(ReservationContract $contract): string
    {
        // This is a placeholder for PDF generation logic
        // In production, you would use a library like DomPDF or wkhtmltopdf
        // For now, return the HTML path as a placeholder
        return $contract->html_path;
    }
}
