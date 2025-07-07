<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Base;
use App\Models\Wishlist;
use App\Models\Book;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Schema(
 *     schema="Wishlist",
 *     title="Wishlist",
 *     description="Wishlist model",
 *     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="book_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
 *     @OA\Property(property="notes", type="string", nullable=true, example="Want to read this book"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
 *     @OA\Property(property="book", ref="#/components/schemas/Book")
 * )
 * 
 * @OA\Schema(
 *     schema="WishlistCheck",
 *     title="Wishlist Check Response",
 *     description="Response for checking if a book is in wishlist",
 *     @OA\Property(property="in_wishlist", type="boolean", example=true),
 *     @OA\Property(property="wishlist_item", ref="#/components/schemas/Wishlist", nullable=true)
 * )
 * 
 * @OA\Tag(
 *     name="Wishlist",
 *     description="Wishlist management endpoints"
 * )
 */

class WishlistController extends Controller
{
    /**
     * Display a listing of the user's wishlist.
     * 
     * @OA\Get(
     *     path="/api/wishlist",
     *     summary="Get user's wishlist",
     *     tags={"Wishlist"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Wishlist retrieved successfully",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Wishlist"))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $wishlist = Wishlist::with('book.category')
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($wishlist);
    }

    /**
     * Add a book to the user's wishlist.
     * 
     * @OA\Post(
     *     path="/api/wishlist",
     *     summary="Add book to wishlist",
     *     tags={"Wishlist"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"book_id"},
     *             @OA\Property(property="book_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
     *             @OA\Property(property="notes", type="string", maxLength=1000, example="Want to read this book")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Book added to wishlist successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Wishlist")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Book already in wishlist",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Book is already in your wishlist")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Book not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'book_id' => 'required|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if book exists
        $book = Book::find($validated['book_id']);
        if (!$book) {
            return response()->json([
                'message' => 'Book not found'
            ], 404);
        }

        // Check if book is already in wishlist
        $existingWishlist = Wishlist::where('user_id', $request->user()->id)
            ->where('book_id', $validated['book_id'])
            ->first();

        if ($existingWishlist) {
            return response()->json([
                'message' => 'Book is already in your wishlist'
            ], 400);
        }

        $wishlist = Wishlist::create([
            'user_id' => $request->user()->id,
            'book_id' => $validated['book_id'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $wishlist->load('book.category');

        return response()->json($wishlist, 201);
    }

    /**
     * Display the specified wishlist item.
     * 
     * @OA\Get(
     *     path="/api/wishlist/{id}",
     *     summary="Get specific wishlist item",
     *     tags={"Wishlist"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Wishlist item ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Wishlist item retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Wishlist")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Wishlist item not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Wishlist item not found")
     *         )
     *     )
     * )
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $wishlist = Wishlist::with('book.category')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$wishlist) {
            return response()->json([
                'message' => 'Wishlist item not found'
            ], 404);
        }

        return response()->json($wishlist);
    }

    /**
     * Update the specified wishlist item.
     * 
     * @OA\Put(
     *     path="/api/wishlist/{id}",
     *     summary="Update wishlist item notes",
     *     tags={"Wishlist"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Wishlist item ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="notes", type="string", maxLength=1000, example="Updated notes for this book")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Wishlist item updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Wishlist")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Wishlist item not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Wishlist item not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $wishlist = Wishlist::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$wishlist) {
            return response()->json([
                'message' => 'Wishlist item not found'
            ], 404);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $wishlist->update($validated);
        $wishlist->load('book.category');

        return response()->json($wishlist);
    }

    /**
     * Remove the specified book from wishlist.
     * 
     * @OA\Delete(
     *     path="/api/wishlist/{id}",
     *     summary="Remove book from wishlist",
     *     tags={"Wishlist"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Wishlist item ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book removed from wishlist successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Book removed from wishlist successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Wishlist item not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Wishlist item not found")
     *         )
     *     )
     * )
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $wishlist = Wishlist::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$wishlist) {
            return response()->json([
                'message' => 'Wishlist item not found'
            ], 404);
        }

        $wishlist->delete();

        return response()->json([
            'message' => 'Book removed from wishlist successfully'
        ], 200);
    }

    /**
     * Check if a book is in user's wishlist.
     * 
     * @OA\Get(
     *     path="/api/wishlist/check/{book_id}",
     *     summary="Check if book is in wishlist",
     *     tags={"Wishlist"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="book_id",
     *         in="path",
     *         required=true,
     *         description="Book ID to check",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Wishlist check completed",
     *         @OA\JsonContent(ref="#/components/schemas/WishlistCheck")
     *     )
     * )
     */
    public function check(Request $request, string $book_id): JsonResponse
    {
        $wishlist = Wishlist::with('book.category')
            ->where('user_id', $request->user()->id)
            ->where('book_id', $book_id)
            ->first();

        return response()->json([
            'in_wishlist' => $wishlist !== null,
            'wishlist_item' => $wishlist
        ]);
    }
} 