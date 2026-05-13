<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreTransactionRequest;
use App\Services\Finance\TransactionService;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    protected TransactionService $service;

    public function __construct(TransactionService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->authorize('transactions.view');
        
        $transactions = $this->service->getTransactions(
            $request->user()->currentTeam,
            $request->all()
        );
        
        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $request->all(),
        ]);
    }

    public function store(StoreTransactionRequest $request)
    {
        $this->authorize('transactions.create');
        
        $transaction = $this->service->createTransaction(
            $request->user()->currentTeam,
            $request->validated()
        );
        
        return response()->json(['data' => $transaction, 'message' => 'Transaction created'], 201);
    }

    public function show(Transaction $transaction)
    {
        $this->authorize('transactions.view', $transaction);
        
        $data = $this->service->getTransactionDetails($transaction);
        
        return Inertia::render('Transactions/Show', $data);
    }

    public function reverse(Transaction $transaction)
    {
        $this->authorize('transactions.reverse', $transaction);
        
        $this->service->reverseTransaction($transaction);
        
        return response()->json(['message' => 'Transaction reversed successfully']);
    }

    public function correct(Request $request, Transaction $transaction)
    {
        $this->authorize('transactions.correct', $transaction);
        
        $request->validate(['correction_reason' => 'required|string']);
        
        $this->service->correctTransaction($transaction, $request->all());
        
        return response()->json(['message' => 'Transaction corrected successfully']);
    }

    public function void(Request $request, Transaction $transaction)
    {
        $this->authorize('transactions.void', $transaction);
        
        $request->validate(['void_reason' => 'required|string']);
        
        $this->service->voidTransaction($transaction, $request->input('void_reason'));
        
        return response()->json(['message' => 'Transaction voided successfully']);
    }

    public function export(Request $request)
    {
        $this->authorize('transactions.export');
        
        return $this->service->exportTransactions($request->all());
    }
}
