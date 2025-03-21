<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdController extends Controller
{
    // Получение списка всех объявлений
    public function index(Request $request)
    {
        $query = Ad::query();

        // Фильтрация по категории
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Фильтрация по статусу объявления
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Фильтрация по цене
        if ($request->has('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }

        if ($request->has('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        // Фильтрация по местоположению (поиск по частичному совпадению)
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        // Поиск по названию
        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        // Пагинация
        $ads = $query->paginate(10);

        return response()->json($ads);
    }

    // Получение конкретного объявления
    public function show($id)
    {
        $ad = Ad::findOrFail($id);

        // Получаем полный URL изображения
        if ($ad->image) {
            $ad->image_url = asset('storage/' . $ad->image);
        }

        return response()->json($ad);
    }

    // Создание нового объявления
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'location' => 'required|string',
            'status' => 'required|string|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Проверка на изображение
        ]);

        // Загрузка изображения, если оно есть
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('ads_images', 'public');
        } else {
            $imagePath = null;
        }

        $ad = Ad::create([
            'user_id' => Auth::id(),
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'location' => $validated['location'],
            'status' => $validated['status'],
            'image' => $imagePath,
        ]);

        return response()->json([
            'message' => 'Ad successfully created',
            'ad' => $ad,
        ], 201);
    }

    // Обновление объявления
    public function update(Request $request, $id)
    {
        $ad = Ad::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'location' => 'sometimes|string',
            'status' => 'sometimes|string|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Обновление данных
        $ad->update($validated);

        // Обновление изображения (если загружено новое)
        if ($request->hasFile('image')) {
            Storage::delete('public/' . $ad->image); // Удаляем старое изображение
            $imagePath = $request->file('image')->store('ads_images', 'public');
            $ad->update(['image' => $imagePath]);
        }

        return response()->json([
            'message' => 'Ad updated successfully',
            'ad' => $ad,
        ]);
    }

    // Удаление объявления
    public function destroy($id)
    {
        $ad = Ad::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        // Удаляем изображение, если оно есть
        if ($ad->image) {
            Storage::delete('public/' . $ad->image);
        }

        $ad->delete();

        return response()->json(['message' => 'Ad deleted successfully']);
    }

    public function adsByCategory($categoryId)
    {
        $ads = Ad::where('category_id', $categoryId)->paginate(10);

        return response()->json($ads);
    }

    public function toggleFavorite($id)
    {
        $ad = Ad::findOrFail($id);
        $user = Auth::user();

        if ($user->favorites()->where('ad_id', $id)->exists()) {
            $user->favorites()->detach($id);
            return response()->json(['message' => 'Ad removed from favorites']);
        } else {
            $user->favorites()->attach($id);
            return response()->json(['message' => 'Ad added to favorites']);
        }
    }

    public function getFavorites()
    {
        $user = auth()->user();
        $favorites = $user->favorites()->paginate(10);

        return response()->json($favorites);
    }

    public function addReview(Request $request, $id)
    {
        $ad = Ad::findOrFail($id);

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $review = Review::create([
            'user_id' => Auth::id(),
            'ad_id' => $ad->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json([
            'message' => 'Review added successfully',
            'review' => $review,
        ]);
    }

    public function getReviews($id)
    {
        $ad = Ad::findOrFail($id);
        $reviews = $ad->reviews()->with('user')->paginate(10);

        return response()->json($reviews);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

}
