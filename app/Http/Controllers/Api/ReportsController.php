<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportsController extends Controller
{
    public function deposits(Request $request)
    {
        return response()->json([
            'data' => $this->generateDummyDeposits()
        ]);
    }

    public function withdraws(Request $request)
    {
        return response()->json([
            'data' => $this->generateDummyWithdraws()
        ]);
    }

    public function safeMovement(Request $request)
    {
        return response()->json([
            'data' => $this->generateDummySafeMovement()
        ]);
    }

    public function customerMovement(Request $request)
    {
        return response()->json([
            'data' => $this->generateDummyCustomerMovement()
        ]);
    }

    public function services(Request $request)
    {
        return response()->json([
            'data' => $this->generateDummyServices()
        ]);
    }

    public function monthly(Request $request)
    {
        return response()->json([
            'data' => $this->generateDummyMonthly()
        ]);
    }

    public function unitsMovement(Request $request)
    {
        return response()->json([
            'data' => $this->generateDummyUnitsMovement()
        ]);
    }

    public function occupancy(Request $request)
    {
        return response()->json([
            'data' => $this->generateDummyOccupancy()
        ]);
    }

    public function cleaning(Request $request)
    {
        return response()->json([
            'data' => $this->generateDummyCleaning()
        ]);
    }

    public function maintenance(Request $request)
    {
        return response()->json([
            'data' => $this->generateDummyMaintenance()
        ]);
    }

    public function transfers(Request $request)
    {
        return response()->json([
            'data' => $this->generateDummyTransfers()
        ]);
    }

    public function revenues(Request $request)
    {
        return response()->json([
            'data' => $this->generateDummyRevenues()
        ]);
    }

    public function resources(Request $request)
    {
        return response()->json([
            'data' => $this->generateDummyResources()
        ]);
    }

    public function contracts(Request $request)
    {
        return response()->json([
            'data' => $this->generateDummyContracts()
        ]);
    }

    public function invoices(Request $request)
    {
        return response()->json([
            'data' => $this->generateDummyInvoices()
        ]);
    }

    public function daily(Request $request)
    {
        return response()->json([
            'data' => $this->generateDummyDaily()
        ]);
    }

    // Data generation methods
    private function generateDummyDeposits()
    {
        return [
            ['id' => 1, 'reference_number' => 'DEP-001', 'date' => '2025-01-15', 'amount' => 5000.00, 'status' => 'Completed'],
            ['id' => 2, 'reference_number' => 'DEP-002', 'date' => '2025-01-16', 'amount' => 3500.00, 'status' => 'Completed'],
            ['id' => 3, 'reference_number' => 'DEP-003', 'date' => '2025-01-17', 'amount' => 7500.00, 'status' => 'Pending'],
            ['id' => 4, 'reference_number' => 'DEP-004', 'date' => '2025-01-18', 'amount' => 4200.00, 'status' => 'Completed'],
            ['id' => 5, 'reference_number' => 'DEP-005', 'date' => '2025-01-19', 'amount' => 6000.00, 'status' => 'Completed'],
        ];
    }

    private function generateDummyWithdraws()
    {
        return [
            ['id' => 1, 'reference_number' => 'WTH-001', 'date' => '2025-01-15', 'amount' => 2000.00, 'status' => 'Completed'],
            ['id' => 2, 'reference_number' => 'WTH-002', 'date' => '2025-01-16', 'amount' => 1500.00, 'status' => 'Completed'],
            ['id' => 3, 'reference_number' => 'WTH-003', 'date' => '2025-01-17', 'amount' => 3000.00, 'status' => 'Pending'],
            ['id' => 4, 'reference_number' => 'WTH-004', 'date' => '2025-01-18', 'amount' => 2500.00, 'status' => 'Completed'],
        ];
    }

    private function generateDummySafeMovement()
    {
        return [
            ['id' => 1, 'reference_number' => 'SM-001', 'date' => '2025-01-15', 'amount' => 10000.00, 'status' => 'Verified'],
            ['id' => 2, 'reference_number' => 'SM-002', 'date' => '2025-01-16', 'amount' => -2500.00, 'status' => 'Verified'],
            ['id' => 3, 'reference_number' => 'SM-003', 'date' => '2025-01-17', 'amount' => 5500.00, 'status' => 'Pending'],
            ['id' => 4, 'reference_number' => 'SM-004', 'date' => '2025-01-18', 'amount' => 3200.00, 'status' => 'Verified'],
        ];
    }

    private function generateDummyCustomerMovement()
    {
        return [
            ['id' => 1, 'reference_number' => 'CM-001', 'date' => '2025-01-15', 'amount' => 1200.00, 'status' => 'Active'],
            ['id' => 2, 'reference_number' => 'CM-002', 'date' => '2025-01-16', 'amount' => 800.00, 'status' => 'Active'],
            ['id' => 3, 'reference_number' => 'CM-003', 'date' => '2025-01-17', 'amount' => 1500.00, 'status' => 'Completed'],
            ['id' => 4, 'reference_number' => 'CM-004', 'date' => '2025-01-18', 'amount' => 950.00, 'status' => 'Active'],
        ];
    }

    private function generateDummyServices()
    {
        return [
            ['id' => 1, 'reference_number' => 'SRV-001', 'date' => '2025-01-15', 'amount' => 450.00, 'status' => 'Completed'],
            ['id' => 2, 'reference_number' => 'SRV-002', 'date' => '2025-01-16', 'amount' => 320.00, 'status' => 'Completed'],
            ['id' => 3, 'reference_number' => 'SRV-003', 'date' => '2025-01-17', 'amount' => 580.00, 'status' => 'Pending'],
            ['id' => 4, 'reference_number' => 'SRV-004', 'date' => '2025-01-18', 'amount' => 275.00, 'status' => 'Completed'],
        ];
    }

    private function generateDummyMonthly()
    {
        return [
            ['id' => 1, 'reference_number' => 'MTH-001', 'date' => '2025-01-31', 'amount' => 45000.00, 'status' => 'Completed'],
            ['id' => 2, 'reference_number' => 'MTH-002', 'date' => '2025-02-28', 'amount' => 52000.00, 'status' => 'Completed'],
            ['id' => 3, 'reference_number' => 'MTH-003', 'date' => '2025-03-31', 'amount' => 48000.00, 'status' => 'Pending'],
        ];
    }

    private function generateDummyUnitsMovement()
    {
        return [
            ['id' => 1, 'reference_number' => 'UM-001', 'date' => '2025-01-15', 'amount' => 0, 'status' => 'Occupied'],
            ['id' => 2, 'reference_number' => 'UM-002', 'date' => '2025-01-16', 'amount' => 0, 'status' => 'Vacant'],
            ['id' => 3, 'reference_number' => 'UM-003', 'date' => '2025-01-17', 'amount' => 0, 'status' => 'Maintenance'],
            ['id' => 4, 'reference_number' => 'UM-004', 'date' => '2025-01-18', 'amount' => 0, 'status' => 'Occupied'],
        ];
    }

    private function generateDummyOccupancy()
    {
        return [
            ['id' => 1, 'reference_number' => 'OCC-001', 'date' => '2025-01-15', 'amount' => 85.5, 'status' => 'Active'],
            ['id' => 2, 'reference_number' => 'OCC-002', 'date' => '2025-01-16', 'amount' => 92.3, 'status' => 'Active'],
            ['id' => 3, 'reference_number' => 'OCC-003', 'date' => '2025-01-17', 'amount' => 78.9, 'status' => 'Active'],
            ['id' => 4, 'reference_number' => 'OCC-004', 'date' => '2025-01-18', 'amount' => 95.1, 'status' => 'Active'],
        ];
    }

    private function generateDummyCleaning()
    {
        return [
            ['id' => 1, 'reference_number' => 'CLN-001', 'date' => '2025-01-15', 'amount' => 0, 'status' => 'Completed'],
            ['id' => 2, 'reference_number' => 'CLN-002', 'date' => '2025-01-16', 'amount' => 0, 'status' => 'In Progress'],
            ['id' => 3, 'reference_number' => 'CLN-003', 'date' => '2025-01-17', 'amount' => 0, 'status' => 'Pending'],
            ['id' => 4, 'reference_number' => 'CLN-004', 'date' => '2025-01-18', 'amount' => 0, 'status' => 'Completed'],
        ];
    }

    private function generateDummyMaintenance()
    {
        return [
            ['id' => 1, 'reference_number' => 'MNT-001', 'date' => '2025-01-15', 'amount' => 500.00, 'status' => 'Completed'],
            ['id' => 2, 'reference_number' => 'MNT-002', 'date' => '2025-01-16', 'amount' => 350.00, 'status' => 'In Progress'],
            ['id' => 3, 'reference_number' => 'MNT-003', 'date' => '2025-01-17', 'amount' => 200.00, 'status' => 'Pending'],
            ['id' => 4, 'reference_number' => 'MNT-004', 'date' => '2025-01-18', 'amount' => 450.00, 'status' => 'Completed'],
        ];
    }

    private function generateDummyTransfers()
    {
        return [
            ['id' => 1, 'reference_number' => 'TRF-001', 'date' => '2025-01-15', 'amount' => 1500.00, 'status' => 'Completed'],
            ['id' => 2, 'reference_number' => 'TRF-002', 'date' => '2025-01-16', 'amount' => 2300.00, 'status' => 'Completed'],
            ['id' => 3, 'reference_number' => 'TRF-003', 'date' => '2025-01-17', 'amount' => 1800.00, 'status' => 'Pending'],
            ['id' => 4, 'reference_number' => 'TRF-004', 'date' => '2025-01-18', 'amount' => 950.00, 'status' => 'Completed'],
        ];
    }

    private function generateDummyRevenues()
    {
        return [
            ['id' => 1, 'reference_number' => 'REV-001', 'date' => '2025-01-15', 'amount' => 12500.00, 'status' => 'Taxed'],
            ['id' => 2, 'reference_number' => 'REV-002', 'date' => '2025-01-16', 'amount' => 15800.00, 'status' => 'Taxed'],
            ['id' => 3, 'reference_number' => 'REV-003', 'date' => '2025-01-17', 'amount' => 11200.00, 'status' => 'Pending'],
            ['id' => 4, 'reference_number' => 'REV-004', 'date' => '2025-01-18', 'amount' => 18900.00, 'status' => 'Taxed'],
        ];
    }

    private function generateDummyResources()
    {
        return [
            ['id' => 1, 'reference_number' => 'RES-001', 'date' => '2025-01-15', 'amount' => 800.00, 'status' => 'Booked'],
            ['id' => 2, 'reference_number' => 'RES-002', 'date' => '2025-01-16', 'amount' => 650.00, 'status' => 'Available'],
            ['id' => 3, 'reference_number' => 'RES-003', 'date' => '2025-01-17', 'amount' => 900.00, 'status' => 'Booked'],
            ['id' => 4, 'reference_number' => 'RES-004', 'date' => '2025-01-18', 'amount' => 550.00, 'status' => 'Available'],
        ];
    }

    private function generateDummyContracts()
    {
        return [
            ['id' => 1, 'reference_number' => 'CTR-001', 'date' => '2025-01-15', 'amount' => 5000.00, 'status' => 'Active'],
            ['id' => 2, 'reference_number' => 'CTR-002', 'date' => '2025-01-16', 'amount' => 4500.00, 'status' => 'Active'],
            ['id' => 3, 'reference_number' => 'CTR-003', 'date' => '2025-01-17', 'amount' => 6000.00, 'status' => 'Expired'],
            ['id' => 4, 'reference_number' => 'CTR-004', 'date' => '2025-01-18', 'amount' => 3500.00, 'status' => 'Active'],
        ];
    }

    private function generateDummyInvoices()
    {
        return [
            ['id' => 1, 'reference_number' => 'INV-001', 'date' => '2025-01-15', 'amount' => 3500.00, 'status' => 'Paid'],
            ['id' => 2, 'reference_number' => 'INV-002', 'date' => '2025-01-16', 'amount' => 4200.00, 'status' => 'Paid'],
            ['id' => 3, 'reference_number' => 'INV-003', 'date' => '2025-01-17', 'amount' => 2800.00, 'status' => 'Pending'],
            ['id' => 4, 'reference_number' => 'INV-004', 'date' => '2025-01-18', 'amount' => 5100.00, 'status' => 'Overdue'],
        ];
    }

    private function generateDummyDaily()
    {
        return [
            ['id' => 1, 'reference_number' => 'DAY-001', 'date' => '2025-01-15', 'amount' => 15000.00, 'status' => 'Completed'],
            ['id' => 2, 'reference_number' => 'DAY-002', 'date' => '2025-01-16', 'amount' => 18200.00, 'status' => 'Completed'],
            ['id' => 3, 'reference_number' => 'DAY-003', 'date' => '2025-01-17', 'amount' => 12500.00, 'status' => 'Completed'],
            ['id' => 4, 'reference_number' => 'DAY-004', 'date' => '2025-01-18', 'amount' => 21000.00, 'status' => 'Completed'],
        ];
    }

    public function unitCleanings(Request $request)
    {
        return response()->json([
            'data' => $this->generateDummyCleaning()
        ]);
    }

    public function unitMaintenance(Request $request)
    {
        return response()->json([
            'data' => $this->generateDummyMaintenance()
        ]);
    }

    public function revenueTax(Request $request)
    {
        return response()->json([
            'data' => $this->generateDummyRevenues()
        ]);
    }
}
