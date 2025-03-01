<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email'    => 'required|email',
                'password' => 'required',
            ]);
            $customer = Customer::where('email', $request->email)->first();

            if (! $customer || ! Hash::check($request->password, $customer->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }
            $token = $customer->createToken('api_token')->plainTextToken;

            return response()->json([
                'token'    => $token,
                'customer' => new CustomerResource($customer),
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            throw ValidationException::withMessages([
                'error' => ['The provided credentials are incorrect1. '.$th->getMessage()],
            ]);
        }
    }
}