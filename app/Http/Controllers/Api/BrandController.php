<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BrandController extends Controller
{
    public function index()
    {
        return response()->json([
            'success'=> true,
            'message' => 'get all Brands',
            'data' =>  BrandResource::collection(Brand::all()),
        ]);
    }

    public function show(string $id)
    {
        try {
            return response()->json([
                'success'=> true,
                'message' => 'get the Brand',
                'data' => new BrandResource(Brand::findOrFail($id)),
            ]);
        } catch (\Throwable $th) {
            throw ValidationException::withMessages([
                'The provided credentials are incorrect1. '.$th->getMessage()
            ]);
        }
        
    }
}