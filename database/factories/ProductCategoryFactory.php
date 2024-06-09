<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'product_id' => Product::factory(), // Membuat atau mengambil ID dari instance Product
            'sub_category_id' => SubCategory::factory(), // Membuat atau mengambil ID dari instance Category
        ];
    }
}