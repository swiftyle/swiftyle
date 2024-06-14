<?php

namespace App\Http\Controllers\Api;

use App\Events\ProductCreated;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSize;
use App\Models\Shop;
use App\Models\SizeColor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // Function to verify seller role and ownership
    private function verifySellerOwnership($userId, $shopId)
    {
        $shop = Shop::where('id', $shopId)->where('user_id', $userId)->first();
        return $shop !== null;
    }

    public function create(Request $request)
    {
        $user = $request->user();

        // Check if the user is a seller
        if (!$this->verifySellerRole($user)) {
            return response()->json(['msg' => 'Hanya seller yang bisa menambahkan produk'], 403);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'rating' => 'required|numeric',
            'main_category_id' => 'required|exists:categories,id',
            'subcategory_ids' => 'required|array',
            'subcategory_ids.*' => 'exists:categories,id',
            'sizes' => 'required|array',
            'sizes.*.size_id' => 'required|exists:sizes,id',
            'sizes.*.quantity' => 'required|numeric|min:1',
            'colors' => 'required|array',
            'colors.*.color_id' => 'required|exists:colors,id',
            'colors.*.quantity' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $validated = $validator->validated();

        // Get the shop associated with the authenticated user
        $shop = Shop::where('user_id', $user->id)->first();

        if (!$shop) {
            return response()->json(['message' => 'Toko tidak ditemukan'], 404);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $filePath = $request->file('image')->store('images', 'public');
            $validated['image'] = $filePath;
        }

        // Begin database transaction
        DB::beginTransaction();

        try {
            // Create the product
            $product = $shop->products()->create($validated);

            // Attach main category to product
            $productCategory = new ProductCategory(['main_category_id' => $validated['main_category_id']]);
            $product->categories()->save($productCategory);

            // Attach subcategories to product
            foreach ($validated['subcategory_ids'] as $subcategoryId) {
                $productCategory->categories()->attach($subcategoryId);
            }

            // Update sizes and quantities
            foreach ($validated['sizes'] as $size) {
                $productSize = new ProductSize(['size_id' => $size['size_id']]);
                $product->sizes()->save($productSize);

                // Update stock table
                ProductSize::updateOrCreate([
                    'product_id' => $product->id,
                    'size_id' => $size['size_id'],
                ]);
            }

            // Update colors and quantities
            foreach ($validated['colors'] as $color) {
                $sizeColor = SizeColor::updateOrCreate([
                    'size_id' => $color['size_id'],
                    'color_id' => $color['color_id'],
                ], [
                    'stock' => $color['stock']
                ]);
            }

            // Commit transaction
            DB::commit();

            // Dispatch event
            event(new ProductCreated($product));

            return response()->json([
                'message' => "Data Berhasil Disimpan",
                'data' => $product
            ], 200);
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            // Log the error
            Log::error('Error creating product: ' . $e->getMessage());

            // Return error response
            return response()->json(['message' => 'Gagal menyimpan data produk'], 500);
        }
    }

    public function read(Request $request)
    {
        $user = $request->user();

        // Initialize an empty array for products
        $products = [];

        // Check if the user is an Admin
        if ($user->role == 'Admin') {
            // Admin can see all products
            $products = Product::all();
        } else {
            // Non-admin users can only see products from their own shop
            $shop = Shop::where('user_id', $user->id)->first();

            if ($shop) {
                $products = Product::where('shop_id', $shop->id)->get();
            }
        }

        // Add logic for user preferences and recommendations based on followed users

        // 1. Retrieve user preferences
        $userPreferences = $this->getUserPreferences($user);

        // 2. Retrieve products based on user preferences
        $preferredProducts = $this->getPreferredProducts($userPreferences);

        // 3. Retrieve products based on followed users' preferences
        $recommendedProducts = $this->getRecommendedProducts($user);

        // Merge products from different sources (e.g., own shop, preferred, recommended)
        $products = $products->merge($preferredProducts)->merge($recommendedProducts);

        return response()->json([
            'message' => 'Data Produk',
            'data' => $products
        ], 200);
    }

    private function getUserPreferences(User $user)
    {
        // Retrieve user preferences based on style_id from preferences table
        return $user->preferences()->pluck('style_id')->toArray();
    }

    private function getPreferredProducts($userPreferences)
    {
        // Retrieve products based on style_id from Product_Style table matching user preferences
        return Product::whereIn('style_id', $userPreferences)->get();
    }

    private function getRecommendedProducts(User $user)
    {
        // Retrieve products based on style_id from preferences of users followed by $user
        $followedUsers = $user->follows()->pluck('id');
        $recommendedProducts = User::whereIn('id', $followedUsers)
                                    ->with('preferences.products') // Assuming preferences relationship with products
                                    ->get()
                                    ->flatMap(function ($user) {
                                        return $user->preferences->flatMap(function ($preference) {
                                            return $preference->products;
                                        });
                                    });

        return $recommendedProducts;
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();

        // Check if the user is a seller
        if (!$this->verifySellerRole($user)) {
            return response()->json(['msg' => 'Hanya seller yang bisa mengupdate produk'], 403);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'rating' => 'sometimes|numeric',
            'main_category_id' => 'sometimes|exists:categories,id',
            'subcategory_ids' => 'sometimes|array',
            'subcategory_ids.*' => 'exists:categories,id',
            'sizes' => 'sometimes|array',
            'sizes.*.size_id' => 'sometimes|exists:sizes,id',
            'sizes.*.quantity' => 'sometimes|numeric|min:1',
            'colors' => 'sometimes|array',
            'colors.*.color_id' => 'sometimes|exists:colors,id',
            'colors.*.quantity' => 'sometimes|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages())->setStatusCode(422);
        }

        $validated = $validator->validated();

        $product = Product::find($id);

        if (!$product) {
            return response()->json(['msg' => 'Data dengan id: ' . $id . ' tidak ditemukan'], 404);
        }

        // Verify ownership
        if (!$product->shop->isOwnedBy($user->id)) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengupdate produk ini'], 403);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if (!is_null($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $filePath = $request->file('image')->store('images', 'public');
            $validated['image'] = $filePath;
        }

        // Begin database transaction
        DB::beginTransaction();

        try {
            // Update the product
            $product->update($validated);

            // Update main category
            if (isset($validated['main_category_id'])) {
                $product->categories()->updateExistingPivot($product->categories()->first()->id, ['main_category_id' => $validated['main_category_id']]);
            }

            // Update subcategories
            if (isset($validated['subcategory_ids'])) {
                $product->categories()->sync($validated['subcategory_ids']);
            }

            // Update sizes and quantities
            if (isset($validated['sizes'])) {
                foreach ($validated['sizes'] as $size) {
                    $productSize = ProductSize::updateOrCreate([
                        'product_id' => $product->id,
                        'size_id' => $size['size_id']
                    ], [
                        'product_id' => $product->id,
                        'size_id' => $size['size_id']
                    ]);

                }
            }

            // Update colors and quantities
            if (isset($validated['colors'])) {
                foreach ($validated['colors'] as $color) {
                    $sizeColor = SizeColor::updateOrCreate([
                        'size_id' => $color['size_id'],
                        'color_id' => $color['color_id'],
                    ], [
                        'stock' => $color['stock']
                    ]);
                }
            }

            // Commit transaction
            DB::commit();

            return response()->json([
                'message' => 'Data dengan id: ' . $id . ' berhasil diupdate',
                'data' => $product
            ], 200);
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            // Log the error
            Log::error('Error updating product: ' . $e->getMessage());

            // Return error response
            return response()->json(['message' => 'Gagal mengupdate data produk'], 500);
        }
    }

    public function delete(Request $request, $id)
    {
        $user = $request->user();

        // Check if the user is a seller
        if ($user->role !== 'Seller') {
            return response()->json(['msg' => 'Hanya seller yang bisa menghapus produk'], 403);
        }

        $product = Product::find($id);

        if (!$product) {
            return response()->json(['msg' => 'Data produk dengan ID: ' . $id . ' tidak ditemukan'], 404);
        }

        // Verify ownership
        if (!$this->verifySellerOwnership($user->id, $product->shop_id)) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus produk ini'], 403);
        }

        // Delete product
        if (!is_null($product->image)) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        return response()->json([
            'msg' => 'Data produk dengan ID: ' . $id . ' berhasil dihapus'
        ], 200);
    }
}
