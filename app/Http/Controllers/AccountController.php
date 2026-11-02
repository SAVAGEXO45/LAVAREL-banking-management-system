<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;

class AccountController extends Controller
{
    // Create a new bank account
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_type' => 'required|in:savings,current',
        ]);

        do {
            $accountNumber = (string) random_int(
                1000000000,
                9999999999
            );
        } while (
            Account::where('account_number', $accountNumber)->exists()
        );

        $account = Account::create([
            'user_id' => auth()->id(),
            'account_number' => $accountNumber,
            'account_type' => $validated['account_type'],
            'balance' => 0.00,
            'currency' => 'NGN',
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bank account created successfully.',
            'account' => $account,
        ], 201);
    }

    // View all accounts belonging to the logged-in customer
    public function index()
    {
        $accounts = Account::where('user_id', auth()->id())->get();

        return response()->json([
            'success' => true,
            'accounts' => $accounts,
        ], 200);
    }

    // Check the balance of a specific account
    public function balance($id)
    {
        $account = Account::where('user_id', auth()->id())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'account_number' => $account->account_number,
            'balance' => $account->balance,
            'currency' => $account->currency,
        ], 200);
    }
}
