<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request, $accountId)
    {
        // Get the authenticated user
        $user = $request->user();

        // Verify that the account belongs to this user
        $account = Account::where('id', $accountId)
            ->where('user_id', $user->id)
            ->first();

        // Reject access if the account does not belong to the user
        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found or access denied.',
            ], 404);
        }

        // Retrieve transactions belonging to the verified account
        $transactions = Transaction::where('account_id', $account->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Transaction history retrieved successfully.',
            'transactions' => $transactions,
        ], 200);
    }
}
