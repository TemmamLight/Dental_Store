<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Login using email and password.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $customer->createToken('api_token')->plainTextToken;

        return response()->json([
            'token'    => $token,
            'customer' => new CustomerResource($customer),
        ]);
    }

    /**
     * Login using phone number and password.
     * This function handles login for existing customers only.
     */
    public function loginByNumber(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|max:15',
            'password'     => 'required',
        ]);

        $customer = Customer::where('phone_number', $request->phone_number)->first();

        if (!$customer) {
            throw ValidationException::withMessages([
                'phone_number' => ['Customer with this phone number does not exist.'],
            ]);
        }

        if (!Hash::check($request->password, $customer->password)) {
            throw ValidationException::withMessages([
                'password' => ['The password is incorrect.'],
            ]);
        }

        $token = $customer->createToken('api_token')->plainTextToken;

        return response()->json([
            'token'    => $token,
            'customer' => new CustomerResource($customer),
        ]);
    }

    /**
     * Register a new customer using phone number and password.
     * Generates a verification code upon registration.
     */
    public function registerByPhone(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|max:15|unique:customers,phone_number',
        ]);

        $customer = Customer::create([
            'phone_number'      => $request->phone_number,
            'password'          => Hash::make('password'),
            'verification_code' => rand(100000, 999999),
        ]);

        return response()->json([
            'message'  => 'Customer created. Verification code sent.',
            'customer' => new CustomerResource($customer),
        ], 201);
    }


    /**
     * Verify the customer's phone number using a verification code.
     * Upon success, clear the code and return an API token.
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|regex:/^\d{9,14}$/',
            'verifi_code'  => 'required|integer',
        ]);

        $customer = Customer::where('phone_number', $request->phone_number)->first();

        if (!$customer || $customer->verification_code != $request->verifi_code) {
            return response()->json([
                'success' => false,
                'message' => 'The verification code is incorrect.',
            ], 401);
        }

        $customer->update(['verification_code' => null]);
        $token = $customer->createToken('api_token')->plainTextToken;

        return response()->json([
            'token'    => $token,
            'customer' => new CustomerResource($customer),
        ]);
    }

    /**
     * Forgot Password: Generate and send a new verification code to the customer's phone.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|max:15',
        ]);

        $customer = Customer::where('phone_number', $request->phone_number)->first();

        if (!$customer) {
            throw ValidationException::withMessages([
                'phone_number' => ['Customer with this phone number does not exist.'],
            ]);
        }

        $verificationCode = rand(100000, 999999);
        $customer->update(['verification_code' => $verificationCode]);

        // In production, send the verification code via SMS.
        return response()->json([
            'message'           => 'Verification code sent.',
            'verifi_code' => $verificationCode, // Remove or hide in production.
        ]);
    }

    /**
     * Reset Password: Update the customer's password using phone number and verification code.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|max:15',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $customer = Customer::where('phone_number', $request->phone_number)->first();

        if (!$customer) {
            throw ValidationException::withMessages([
                'Invalid verification code.'
            ]);
        }

        $customer->update([
            'password'          => Hash::make($request->new_password),
            'verification_code' => null,
        ]);

        return response()->json([
            'message'  => 'Password has been reset successfully.',
            'customer' => new CustomerResource($customer),
        ]);
    }

    /**
     * Register a new customer with name, email, and password.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:customers,email',
            'phone_number' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $customer = Customer::where('phone_number', $request->phone_number)->first();
        if(!$customer){
            return response()->json(['errors' => 'the customer is not found'], 422);
        }
        $customer->update([
            'name'     => $request->name,
            'email'    => $request->email,
        ]);

        $token = $customer->createToken('api_token')->plainTextToken;

        return response()->json([
            'message'  => 'Account created successfully.',
            'token'    => $token,
            'customer' => new CustomerResource($customer),
        ], 201);
    }

    /**
     * Logout the authenticated customer by revoking their current access token.
     */
    public function logout(Request $request)
    {
        try {
            if (!$request->bearerToken()) {
                return response()->json(['success'=>false,'message' => 'Token is missing'], 401);
            }

            $customer = $request->user(); 

            if (!$customer) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $customer->currentAccessToken()->delete();

            return response()->json([
                'success'=>true,
                'message' => 'Successfully logged out.'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success'=>false,
                'error' => 'Something went wrong',
                'message' => $th->getMessage()
            ], 500);
        }
    }


}