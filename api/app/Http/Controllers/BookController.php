<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // GET api/books
    public function index(): JsonResponse
    {
        return response()->json(Book::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    // POST api/books
    public function store(Request $request): JsonResponse
    {
        $validated_book = $request->validate([
            'title' => 'required|string|max:256',
            'author' => 'required|string|max:256',
        ]);
        return response()->json(Book::create($validated_book), 201);
    }

    /**
     * Display the specified resource.
     */
    // GET api/books/{id}
    public function show(string $id): JsonResponse
    {
        $book = Book::find($id);
        
        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }
        
        return response()->json($book);
    }

    /**
     * Update the specified resource in storage.
     */
    // PUT/PATCH api/books/{id}
    public function update(Request $request, string $id): JsonResponse
    {
        $book = Book::find($id);
        
        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }
        
        $validated_data = $request->validate([
            'title' => 'sometimes|required|string|max:256',
            'author' => 'sometimes|required|string|max:256',
        ]);
        
        $book->update($validated_data);
        
        return response()->json($book);
    }

    /**
     * Remove the specified resource from storage.
     */
    // DELETE api/books/{id}
    public function destroy(string $id): JsonResponse
    {
        $book = Book::find($id);
        
        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }
        
        $book->delete();
        
        return response()->json(['message' => 'Book deleted successfully'], 200);
    }
}
