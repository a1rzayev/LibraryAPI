<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Book;
use Illuminate\Http\JsonResponse;

class WishlistController extends Controller
{
    /**
     * Display a listing of the user's wishlist.
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