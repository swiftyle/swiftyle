<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubCategoryController extends Controller
{
    public function create(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'main_category_id' => 'required|exists:main_categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $validated = $validator->validated();

        $userId = $user->id;
        $userEmail = $user->email;

        // Ensure user ID is not null before proceeding
        if (!$userId) {
            return response()->json(['message' => 'User ID not found'], 401);
        }

        // Add user email as modified_by
        $validated['modified_by'] = $userEmail;

        // Create the subcategory record
        $subCategory = SubCategory::create($validated);

        return response()->json([
            'message' => "SubCategory successfully created",
            'data' => $subCategory
        ], 201);
    }


    public function read(Request $request)
    {
        $user = $request->user();
        $subCategories = SubCategory::with('mainCategory')->get();
        return response()->json([
            'message' => 'List of all subcategories',
            'data' => $subCategories
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'main_category_id' => 'sometimes|exists:main_category,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $validated = $validator->validated();

        $userId = $user->id;
        $userEmail = $user->email;

        // Ensure user ID is not null before proceeding
        if (!$userId) {
            return response()->json(['message' => 'User ID not found'], 401);
        }

        // Add user email as modified_by
        $validated['modified_by'] = $userEmail;

        $subCategory = SubCategory::find($id);

        if (!$subCategory) {
            return response()->json(['message' => 'SubCategory not found'], 404);
        }

        $subCategory->update($validated);

        return response()->json([
            'message' => "SubCategory successfully updated",
            'data' => $subCategory
        ], 200);
    }

    public function delete(Request $request, $id)
    {
        $user = $request->user();
        $subCategory = SubCategory::find($id);

        if (!$subCategory) {
            return response()->json(['message' => 'SubCategory not found'], 404);
        }

        $subCategory->delete();

        return response()->json([
            'message' => "SubCategory successfully deleted"
        ], 200);
    }
}
