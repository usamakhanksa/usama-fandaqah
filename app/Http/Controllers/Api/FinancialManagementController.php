<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\CreditNote;
use App\Models\Expense;
use App\Models\FundMovement;
use App\Models\Receipt;
use App\User;
use Illuminate\Http\Request;

class FinancialManagementController extends Controller
{
    public function index(Request $request, string $module)
    {
        return response()->json([
            'metrics' => $this->metrics($module),
            'rows' => $this->rows($module, $request),
        ]);
    }

    public function storeDraft(Request $request, string $type)
    {
        $data = $request->validate([
            'payload' => ['required', 'array'],
            'current_step' => ['required', 'integer', 'min:1', 'max:2'],
        ]);

        $class = $type === 'expense' ? \App\Models\ExpenseDraft::class : \App\Models\ReceiptDraft::class;

        $draft = $class::query()->create([
            'user_id' => optional($request->user())->id,
            'payload' => $data['payload'],
            'current_step' => $data['current_step'],
        ]);

        return response()->json($draft, 201);
    }

    public function confirm(Request $request, string $type)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'receipt_type' => ['nullable', 'string', 'max:255'],
            'receipt_code' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:255'],
            'received_by' => ['nullable', 'string', 'max:255'],
            'attachment_path' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $employeeId = User::query()->where('name', $data['received_by'] ?? '')->value('id') ?? User::query()->value('id');

        if ($type === 'expense') {
            $model = Expense::query()->create([
                'expense_number' => 'EXP'.str_pad((string) (Expense::max('id') + 1), 5, '0', STR_PAD_LEFT),
                'expense_type' => $data['receipt_type'] ?? 'General',
                'expense_code' => $data['receipt_code'] ?? null,
                'reason' => $data['reason'] ?? 'General',
                'amount' => $data['amount'],
                'employee_id' => $employeeId,
                'date' => $data['date'],
                'payment_method' => $data['payment_method'],
                'status' => 'confirmed',
                'attachment_path' => $data['attachment_path'] ?? null,
                'note' => $data['note'] ?? null,
            ]);
        } else {
            $model = Receipt::query()->create([
                'receipt_number' => 'REC'.str_pad((string) (Receipt::max('id') + 1), 5, '0', STR_PAD_LEFT),
                'receipt_type' => $data['receipt_type'] ?? 'Rent',
                'receipt_code' => $data['receipt_code'] ?? null,
                'reason' => $data['reason'] ?? 'Rent',
                'amount' => $data['amount'],
                'employee_id' => $employeeId,
                'date' => $data['date'],
                'payment_method' => $data['payment_method'],
                'status' => 'confirmed',
                'attachment_path' => $data['attachment_path'] ?? null,
                'note' => $data['note'] ?? null,
            ]);
        }

        FundMovement::query()->create([
            'movement_type' => $type === 'expense' ? 'bills_of_exchange' : 'receipt',
            'reference_number' => $type === 'expense' ? $model->expense_number : $model->receipt_number,
            'reason' => $data['reason'] ?? 'General',
            'amount' => $data['amount'],
            'employee_id' => $employeeId,
            'date' => $data['date'],
            'payment_method' => $data['payment_method'],
            'status' => 'confirmed',
        ]);

        return response()->json($model, 201);
    }

    private function rows(string $module, Request $request): array
    {
        $perPage = $request->integer('per_page', 10);

        if ($module === 'bills') {
            return Bill::query()->with('employee:id,name')->orderByDesc('id')->paginate($perPage)->toArray();
        }

        if ($module === 'credit-notes') {
            return CreditNote::query()->with('employee:id,name')->orderByDesc('id')->paginate($perPage)->toArray();
        }

        if ($module === 'fund-movement') {
            $view = $request->string('view', 'receipt')->value();
            return FundMovement::query()->with('employee:id,name')->where('movement_type', $view === 'receipt' ? 'receipt' : 'bills_of_exchange')->orderByDesc('id')->paginate($perPage)->toArray();
        }

        if ($module === 'expenses') {
            return Expense::query()->with('employee:id,name')->orderByDesc('id')->paginate($perPage)->toArray();
        }

        return Receipt::query()->with('employee:id,name')->orderByDesc('id')->paginate($perPage)->toArray();
    }

    private function metrics(string $module): array
    {
        if ($module === 'bills') {
            return [
                ['label' => 'Total Have To Collected', 'value' => (float) Bill::sum('amount'), 'color' => 'black'],
                ['label' => 'Total Collected', 'value' => (float) Bill::where('status', 'collected')->sum('amount'), 'color' => 'green'],
                ['label' => 'Total Not charged', 'value' => (float) Bill::where('status', 'not_done')->sum('amount'), 'color' => 'red'],
            ];
        }

        if ($module === 'credit-notes') {
            return [
                ['label' => 'Total Notes', 'value' => (float) CreditNote::sum('amount'), 'color' => 'black'],
                ['label' => 'Today Notes', 'value' => (float) CreditNote::whereDate('creation_date', today())->sum('amount'), 'color' => 'green'],
            ];
        }

        if ($module === 'fund-movement') {
            return [
                ['label' => 'Total Receipts', 'value' => (float) FundMovement::where('movement_type', 'receipt')->sum('amount'), 'color' => 'black'],
                ['label' => 'Total Cost', 'value' => (float) FundMovement::where('movement_type', 'bills_of_exchange')->sum('amount'), 'color' => 'green'],
                ['label' => 'Fund Balance', 'value' => (float) FundMovement::sum('amount'), 'color' => 'yellow'],
                ['label' => 'Total Balance', 'value' => (float) (Receipt::sum('amount') - Expense::sum('amount')), 'color' => 'green'],
            ];
        }

        if ($module === 'expenses') {
            return [
                ['label' => 'Total Amount', 'value' => (float) Expense::sum('amount'), 'color' => 'black'],
                ['label' => 'Total Cash', 'value' => (float) Expense::where('payment_method', 'Cash')->sum('amount'), 'color' => 'green'],
                ['label' => 'Total Bank Transfer', 'value' => (float) Expense::where('payment_method', 'Bank Transfer')->sum('amount'), 'color' => 'green'],
                ['label' => 'Total Payment Card', 'value' => (float) Expense::where('payment_method', 'Credit Card')->sum('amount'), 'color' => 'green'],
                ['label' => 'Total Mada', 'value' => (float) Expense::where('payment_method', 'Mada')->sum('amount'), 'color' => 'green'],
                ['label' => 'Total Agal', 'value' => (float) Expense::where('payment_method', 'Agal')->sum('amount'), 'color' => 'green'],
            ];
        }

        return [
            ['label' => 'Total Amount', 'value' => (float) Receipt::sum('amount'), 'color' => 'black'],
            ['label' => 'Total Cash', 'value' => (float) Receipt::where('payment_method', 'Cash')->sum('amount'), 'color' => 'green'],
            ['label' => 'Total Bank Transfer', 'value' => (float) Receipt::where('payment_method', 'Bank Transfer')->sum('amount'), 'color' => 'green'],
            ['label' => 'Total Payment Card', 'value' => (float) Receipt::where('payment_method', 'Credit Card')->sum('amount'), 'color' => 'green'],
            ['label' => 'Total Mada', 'value' => (float) Receipt::where('payment_method', 'Mada')->sum('amount'), 'color' => 'green'],
            ['label' => 'Total Agal', 'value' => (float) Receipt::where('payment_method', 'Agal')->sum('amount'), 'color' => 'green'],
        ];
    }
}
