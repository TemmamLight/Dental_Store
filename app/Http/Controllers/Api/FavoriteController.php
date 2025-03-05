<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Resources\FavoriteResource;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    /**
     * Display all favorites for the authenticated customer (using customer_id).
     */
    public function index(Request $request)
    {
        $customer_id = $request->customer_id;

        $favorites = Favorite::where('customer_id', $customer_id)
            ->where('favoriteable_type', Category::class) // Only categories
            ->with('favoriteable') // Load the category
            ->get();

        // Filter out deleted categories
        foreach ($favorites as $favorite) {
            if (!$favorite->favoriteable) {
                // If category is deleted, remove it from favorites
                $favorite->delete();
            }
        }

        // Return the valid favorites using a Resource Collection
        return FavoriteResource::collection($favorites);
    }

    /**
     * Add a category to favorites (using customer_id).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id'      => 'required|integer',
            'favoriteable_id'  => 'required|integer|exists:categories,id', // Ensure the category exists
            'favoriteable_type'=> 'required|string|in:' . Category::class,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if the category exists
        $category = Category::find($request->favoriteable_id);

        if (!$category) {
            return response()->json(['message' => 'The specified category does not exist.'], 404);
        }

        // Add the category to favorites
        $favorite = Favorite::firstOrCreate([
            'customer_id'      => $request->customer_id,
            'favoriteable_id'  => $request->favoriteable_id,
            'favoriteable_type'=> Category::class,
        ]);

        return new FavoriteResource($favorite);
    }

    /**
     * Remove a specific favorite category (using customer_id).
     */
    public function destroy($id, Request $request)
    {
        $customer_id = $request->customer_id;

        $favorite = Favorite::where('id', $id)
            ->where('customer_id', $customer_id)
            ->where('favoriteable_type', Category::class)
            ->first();

        if (!$favorite) {
            return response()->json(['message' => 'Favorite not found.'], 404);
        }

        // Check if the category exists
        $category = $favorite->favoriteable;

        if (!$category) {
            // If category was deleted, remove it from the favorites
            $favorite->delete();
            return response()->json(['message' => 'The category no longer exists, removed from favorites.'], 404);
        }

        // Delete the favorite if the category is valid
        $favorite->delete();

        return response()->json(['message' => 'Favorite item has been removed'], 200);
    }

    /**
     * Remove all favorite categories for the authenticated customer (using customer_id).
     */
    public function destroyAll(Request $request)
    {
        $customer_id = $request->customer_id;

        // Remove all favorites for the given customer ID
        $favorites = Favorite::where('customer_id', $customer_id)
            ->where('favoriteable_type', Category::class) // Only categories
            ->get();

        // Check and remove any favorites pointing to deleted categories
        foreach ($favorites as $favorite) {
            if (!$favorite->favoriteable) {
                // If category is deleted, remove it from favorites
                $favorite->delete();
            }
        }

        // Delete all valid favorites for the customer
        Favorite::where('customer_id', $customer_id)
            ->where('favoriteable_type', Category::class)
            ->delete();

        return response()->json(['message' => 'All favorite categories have been removed'], 200);
    }
}