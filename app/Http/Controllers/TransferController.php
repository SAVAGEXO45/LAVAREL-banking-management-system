<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransferController extends Controller
{
    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'sender_account' => 'required|string',
            'receiver_account' => 'required|string|different:sender_account',
            'amount' => 'required|numeric|min:1',
        ]);

        return DB::transaction(function () use ($validated) {

            $accounts = Account::whereIn('account_number', [
                $validated['sender_account'],
                $validated['receiver_account'],
            ])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('account_number');

            $sender = $accounts->get($validated['sender_account']);
            $receiver = $accounts->get($validated['receiver_account']);

            if (!$sender || !$receiver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sender or receiver account not found.',
                ], 404);
            }

            if ($sender->status !== 'active' || $receiver->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Both accounts must be active.',
                ], 403);
            }

            $amount = $validated['amount'];

            if ($amount > $sender->balance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient account balance.',
                ], 400);
            }

            $senderBalanceBefore = $sender->balance;
            $receiverBalanceBefore = $receiver->balance;

            $sender->balance -= $amount;
            $receiver->balance += $amount;

            $sender->save();
            $receiver->save();

            $senderReference = 'TRF-' . strtoupper(Str::random(20));
            $receiverReference = 'TRF-' . strtoupper(Str::random(20));

            Transaction::create([
                'account_id' => $sender->id,
                'reference' => $senderReference,
                'type' => 'withdrawal',
                'amount' => $amount,
                'balance_before' => $senderBalanceBefore,
                'balance_after' => $sender->balance,
                'description' => 'Transfer to ' . $receiver->account_number,
            ]);

            Transaction::create([
                'account_id' => $receiver->id,
                'reference' => $receiverReference,
                'type' => 'deposit',
                'amount' => $amount,
                'balance_before' => $receiverBalanceBefore,
                'balance_after' => $receiver->balance,
                'description' => 'Transfer from ' . $sender->account_number,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transfer successful.',
                'sender_reference' => $senderReference,
                'receiver_reference' => $receiverReference,
                'amount' => number_format($amount, 2, '.', ''),
                'sender_new_balance' => $sender->balance,
                'receiver_new_balance' => $receiver->balance,
            ], 200);
        });
    }
}
