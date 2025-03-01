<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CategoryCollection;

class CategoryController extends Controller
{
    
    public function index()
    {
        $categories = Category::with(['products', 'children', 'parent'])->get();
        return new CategoryCollection($categories);
    }
    public function mainCategories()
    {
        $categories = Category::whereNull('parent_id')->with('children')->get();
        return new CategoryCollection($categories);
    }
    public function subCategories($parentId)
    {
        $categories = Category::where('parent_id', $parentId)->with('children')->get();
        return new CategoryCollection($categories);
    }
    
    public function show($id)
    {
        try {
            $category = Category::with(['products', 'children', 'parent'])->findOrFail($id);
            return new CategoryResource($category);
        } catch (\Exception $e) {
            return response()->json([
                'message' => ' Category is not found',
                'error'   => $e->getMessage()
            ], 404);
        }
    }
}