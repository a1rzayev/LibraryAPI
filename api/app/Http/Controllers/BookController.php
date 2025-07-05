<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // GET api/books
    public function index()
    {
        return Book::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    // POST api/books
    public function store(Request $request)
    {
        $validated_book = $request->validate([
            'title' => 'required|string|nax:256',
            'author' => 'required|string|nax:256',
        ]);
        return Book::create($validated_book);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
