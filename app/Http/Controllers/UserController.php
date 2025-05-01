<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Gate::allows('only-admins')) {
            return response()->json(['message' => 'You are not authorized to perform this action.'], 403);
        }

        return response()->json(User::latest()->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Gate::allows('only-admins')) {
            return response()->json(['message' => 'You are not authorized to perform this action.'], 403);
        }

        $fields = $request->validate([
            'first_name' => 'string|required',
            'last_name' => 'string|required',
            'email' => 'email|required|unique:users',
            'password' => 'string|required|min:8',
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);

        $resource = User::create($fields);

        return response()->json([
            'success' => true,
            'data' => $resource,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (!Gate::allows('only-admins')) {
            return response()->json(['message' => 'You are not authorized to perform this action.'], 403);
        }

        $resource = User::find($id);

        if (!$resource) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Resource not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $resource,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!Gate::allows('only-admins')) {
            return response()->json(['message' => 'You are not authorized to perform this action.'], 403);
        }

        $resource = User::find($id);

        if (!$resource) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Resource not found',
            ], 404);
        }

        $fields = $request->validate([
            'first_name' => 'string|required',
            'last_name' => 'string|required',
            'password' => 'nullable|string|min:8',
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);

        if (empty($fields['password'])) {
            unset($fields['password']);
        }

        $resource->update($fields);

        return response()->json([
            'success' => true,
            'data' => $resource,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!Gate::allows('only-admins')) {
            return response()->json(['message' => 'You are not authorized to perform this action.'], 403);
        }

        $resource = User::find($id);

        if (!$resource) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Resource not found',
            ], 404);
        }

        if ($resource->articles()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete user who has created articles.',
            ], 400);
        }

        $resource->delete();

        return response(status: 204);
    }
}
