<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Library API",
 *     version="1.0.0",
 *     description="API for Library Management System"
 * )
 * 
 * @OA\Schema(
 *     schema="Book",
 *     title="Book",
 *     description="Book model",
 *     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="title", type="string", maxLength=256, example="The Great Gatsby"),
 *     @OA\Property(property="author", type="string", maxLength=256, example="F. Scott Fitzgerald"),
 *     @OA\Property(property="category_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z")
 * )
 */

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @OA\Get(
     *     path="/api/books",
     *     summary="Get all books",
     *     tags={"Books"},
     *     @OA\Response(
     *         response=200,
     *         description="List of books retrieved successfully",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Book"))
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json(Book::all());
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @OA\Post(
     *     path="/api/books",
     *     summary="Create a new book",
     *     tags={"Books"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","author"},
     *             @OA\Property(property="title", type="string", maxLength=256, example="The Great Gatsby"),
     *             @OA\Property(property="author", type="string", maxLength=256, example="F. Scott Fitzgerald"),
     *             @OA\Property(property="category_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Book created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Book")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated_book = $request->validate([
            'title' => 'required|string|max:256',
            'author' => 'required|string|max:256',
            'category_id' => 'nullable|exists:categories,id',
        ]);
        return response()->json(Book::create($validated_book), 201);
    }

    /**
     * Display the specified resource.
     * 
     * @OA\Get(
     *     path="/api/books/{id}",
     *     summary="Get a specific book",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Book ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Book")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found"
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        $book = Book::find($id);
        
        if (!$book) {
            return response()->json([
                'message' => 'Book not found'
            ], 404);
        }
        
        return response()->json($book);
    }

    /**
     * Update the specified resource in storage.
     * 
     * @OA\Put(
     *     path="/api/books/{id}",
     *     summary="Update a book",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Book ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", maxLength=256, example="Updated Book Title"),
     *             @OA\Property(property="author", type="string", maxLength=256, example="Updated Author Name"),
     *             @OA\Property(property="category_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Book")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $book = Book::find($id);
        
        if (!$book) {
            return response()->json([
                'message' => 'Book not found'
            ], 404);
        }
        
        $validated_data = $request->validate([
            'title' => 'sometimes|required|string|max:256',
            'author' => 'sometimes|required|string|max:256',
            'category_id' => 'sometimes|required|exists:categories,id',
        ]);
        
        $book->update($validated_data);
        
        return response()->json($book);
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @OA\Delete(
     *     path="/api/books/{id}",
     *     summary="Delete a book",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Book ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Book deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found"
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $book = Book::find($id);
        
        if (!$book) {
            return response()->json([
                'message' => 'Book not found'
            ], 404);
        }
        
        $book->delete();
        
        return response()->json([
            'message' => 'Book deleted successfully'
        ], 200);
    }
    
}
