<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::user()->role === UserRole::ADMIN) {
            return response()->json(Article::with(['user:id,first_name,last_name,email'])->paginate());
        }

        return response()->json(Article::where('user_id', Auth::user()->id)->with(['user:id,first_name,last_name,email'])->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'title' => 'string|required',
            'content' => 'string|required',
        ]);

        $resource = Article::create([
            ...$fields,
            'user_id' => Auth::user()->id,
        ]);

        return response()->json([
            'success' => true,
            'data' => $resource,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        if (!$article) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Article not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $article,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Article $article)
    {
        if (!Gate::allows('article-manage', $article)) {
            return response()->json(['message' => 'You are not authorized to perform this action.'], 403);
        }

        if (!$article) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Article not found',
            ], 404);
        }

        $fields = $request->validate([
            'title' => 'string|required',
            'content' => 'string|required',
        ]);

        $article->update($fields);

        return response()->json([
            'success' => true,
            'data' => $article,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        if (!Gate::allows('article-manage', $article)) {
            return response()->json(['message' => 'You are not authorized to perform this action.'], 403);
        }

        if (!$article) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Article not found',
            ], 404);
        }

        $article->delete();

        return response(status: 204);
    }
}
