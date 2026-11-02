<?php
namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WithdrawalController extends Controller
{
    public function withdraw(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        return DB::transaction(function () use ($validated, $id, $request) {

            $account = Account::where('id', $id)
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($account->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'This account is not active.',
                ], 403);
            }

            $amount = $validated['amount'];
            $balanceBefore = $account->balance;

            if ($amount > $balanceBefore) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient account balance.',
                ], 400);
            }

            $account->balance = $balanceBefore - $amount;
            $account->save();

            $transaction = Transaction::create([
                'account_id' => $account->id,
                'reference' => 'WTH-' . strtoupper(Str::random(12)),
                'type' => 'withdrawal',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $account->balance,
                'description' => 'Account withdrawal',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal successful.',
                'transaction' => $transaction,
                'new_balance' => $account->balance,
            ], 200);
        });
    }
}
