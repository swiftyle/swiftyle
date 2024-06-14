<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wishlist;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    public function create(Request $request)
    {
        try {
            // Decode JWT token to get user data
            $data = JWT::decode($request->bearerToken(), new Key(env('JWT_SECRET_KEY'), 'HS256'));
            $user = User::find($data->id);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages())->setStatusCode(422);
        }

        // Create the wishlist
        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'name' => $request->input('name'),
        ]);

        return response()->json([
            'message' => 'Wishlist created successfully',
            'data' => $wishlist
        ], 201);
    }

    public function read($request)
    {
        try {
            // Decode JWT token to get user data
            $data = JWT::decode($request->bearerToken(), new Key(env('JWT_SECRET_KEY'), 'HS256'));
            $user = User::find($data->id);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Fetch wishlists belonging to the authenticated user
        $wishlists = Wishlist::where('user_id', $user->id)->get();

        return response()->json([
            'message' => 'Wishlists fetched successfully',
            'data' => $wishlists
        ], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            // Decode JWT token to get user data
            $data = JWT::decode($request->bearerToken(), new Key(env('JWT_SECRET_KEY'), 'HS256'));
            $user = User::find($data->id);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages())->setStatusCode(422);
        }

        // Find the wishlist
        $wishlist = Wishlist::where('user_id', $user->id)->find($id);

        if ($wishlist) {
            // Update the wishlist
            $wishlist->update([
                'name' => $request->input('name'),
            ]);

            return response()->json([
                'message' => 'Wishlist updated successfully',
                'data' => $wishlist
            ], 200);
        }

        return response()->json([
            'message' => 'Wishlist not found'
        ], 404);
    }

    public function delete(Request $request, $id)
    {
        try {
            // Decode JWT token to get user data
            $data = JWT::decode($request->bearerToken(), new Key(env('JWT_SECRET_KEY'), 'HS256'));
            $user = User::find($data->id);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Find the wishlist
        $wishlist = Wishlist::where('user_id', $user->id)->find($id);

        if ($wishlist) {
            // Delete the wishlist
            $wishlist->delete();

            return response()->json([
                'message' => 'Wishlist deleted successfully'
            ], 200);
        }

        return response()->json([
            'message' => 'Wishlist not found'
        ], 404);
    }
}