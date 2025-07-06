<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Category",
 *     title="Category",
 *     description="Category model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", maxLength=256, example="Fiction"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z")
 * )
 */

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     * 
     * @OA\Get(
     *     path="/api/categories",
     *     summary="Get all categories",
     *     tags={"Categories"},
     *     @OA\Response(
     *         response=200,
     *         description="List of categories retrieved successfully",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Category"))
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json(Category::all());
    }

    /**
     * Store a newly created category.
     * 
     * @OA\Post(
     *     path="/api/categories",
     *     summary="Create a new category (Admin only)",
     *     description="Only users with the admin role can create categories.",
     *     tags={"Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=256, example="Fiction")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Category")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated_category = $request->validate([
            'name' => 'required|string|max:256',
        ]);
        return response()->json(Category::create($validated_category), 201);
    }

    /**
     * Display the specified category.
     * 
     * @OA\Get(
     *     path="/api/categories/{id}",
     *     summary="Get a specific category",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Category ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Category")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        $category = Category::find($id);
        if(!$category){
            return response()->json([
                'message' => "Category not found"
            ], 404);
        }
        return response()->json($category);
    }

    /**
     * Update the specified category.
     * 
     * @OA\Put(
     *     path="/api/categories/{id}",
     *     summary="Update a category (Admin only)",
     *     description="Only users with the admin role can update categories.",
     *     tags={"Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Category ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", maxLength=256, example="Updated Category Name")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Category")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $category = Category::find($id);
        if(!$category){
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }
        $validated_data = $request->validate([
            'name' => 'sometimes|required|string|max:256',
        ]);
        $category->update($validated_data);
        return response()->json($category);
    }
    /**
     * Remove the specified category.
     * 
     * @OA\Delete(
     *     path="/api/categories/{id}",
     *     summary="Delete a category (Admin only)",
     *     description="Only users with the admin role can delete categories.",
     *     tags={"Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Category ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $category = Category::find($id);
        if(!$category){
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully'], 200);
    }

    /**
     * Get all books for a specific category.
     * 
     * @OA\Get(
     *     path="/api/categories/{id}/books",
     *     summary="Get books by category",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Category ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Books retrieved successfully",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Book"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */
    public function getBooks(string $id): JsonResponse
    {
        $category = Category::find($id);
        if(!$category){
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }
        return response()->json($category->books);
    }
}
