<?php

namespace App\Infrastructure\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $category = Category::create($validated);
        
        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }
    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $category->update($validated);
        
        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category
        ]);
    }
    public function destroy(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        
        // Check if there are any tickets using this category
        if ($category->tickets()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category as it has associated tickets'
            ], 409); // Conflict status code
        }
        
        $category->delete();
        
        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }
}