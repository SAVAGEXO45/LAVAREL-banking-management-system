<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    // Customer Registration
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'account_type' => 'sometimes|in:savings,current',
        ]);

        $user = DB::transaction(function () use ($validated) {

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            do {
                $accountNumber = (string) random_int(
                    1000000000,
                    9999999999
                );
            } while (
                Account::where('account_number', $accountNumber)->exists()
            );

            $user->accounts()->create([
                'account_number' => $accountNumber,
                'account_type' => $validated['account_type'] ?? 'savings',
                'balance' => 0.00,
                'currency' => 'NGN',
                'status' => 'active',
            ]);

            return $user;
        });

        return response()->json([
            'success' => true,
            'message' => 'Customer registered successfully.',
            'customer' => $user->load('accounts'),
        ], 201);
    }

    // Customer Login
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $token = $user->createToken('banking-api')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'token' => $token,
            'customer' => $user->load('accounts'),
        ], 200);
    }
}
