<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\{Book, Hadith};
use Illuminate\Support\Facades\Cache;

// ✅ كل الكتب
Route::get('/books', function () {
    return Cache::rememberForever('books_list', function () {
        return [
            'total' => Book::count(),
            'data' => Book::select('id', 'name', 'num_of_hadiths',"download_url","created_at")->get()
        ];
    });
});

// ✅ أحاديث كتاب معين مع Pagination + Cache
Route::get('/books/{id}', function ($id, Request $request) {
    $page = $request->query('page', 1);
    $limit = $request->query('limit', 20);
    $cacheKey = "book_{$id}_page_{$page}_limit_{$limit}";

    return Cache::rememberForever($cacheKey, function () use ($id, $limit) {
        $book = Book::findOrFail($id);
        $hadiths = Hadith::where('book_id', $id)->paginate($limit);

        return [
            'book' => $book,
            'pagination' => [
                'total' => $hadiths->total(),
                'per_page' => $hadiths->perPage(),
                'current_page' => $hadiths->currentPage(),
                'last_page' => $hadiths->lastPage()
            ],
            'data' => $hadiths->items()
        ];
    });
});
