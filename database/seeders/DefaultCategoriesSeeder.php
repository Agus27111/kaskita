<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Family;
use Illuminate\Database\Seeder;

class DefaultCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $families = Family::all();

        $expenseCategories = [
            ['name' => 'Transportasi', 'icon' => 'car', 'color' => '#3b82f6'],
            ['name' => 'Makan & Minum', 'icon' => 'utensils', 'color' => '#f59e0b'],
            ['name' => 'Belanja', 'icon' => 'shopping-bag', 'color' => '#ec4899'],
            ['name' => 'Tagihan & Utilitas', 'icon' => 'zap', 'color' => '#8b5cf6'],
            ['name' => 'Kesehatan', 'icon' => 'heart', 'color' => '#ef4444'],
            ['name' => 'Pendidikan', 'icon' => 'book', 'color' => '#06b6d4'],
            ['name' => 'Hiburan', 'icon' => 'gamepad', 'color' => '#f97316'],
            ['name' => 'Lainnya', 'icon' => 'more-horizontal', 'color' => '#71717a'],
        ];

        $incomeCategories = [
            ['name' => 'Gaji', 'icon' => 'briefcase', 'color' => '#059669'],
            ['name' => 'Freelance', 'icon' => 'laptop', 'color' => '#0ea5e9'],
            ['name' => 'Bisnis', 'icon' => 'store', 'color' => '#8b5cf6'],
            ['name' => 'Investasi', 'icon' => 'trending-up', 'color' => '#f59e0b'],
            ['name' => 'Lainnya', 'icon' => 'more-horizontal', 'color' => '#71717a'],
        ];

        foreach ($families as $family) {
            foreach ($expenseCategories as $cat) {
                Category::firstOrCreate(
                    ['family_id' => $family->id, 'name' => $cat['name'], 'type' => 'expense'],
                    $cat
                );
            }

            foreach ($incomeCategories as $cat) {
                Category::firstOrCreate(
                    ['family_id' => $family->id, 'name' => $cat['name'], 'type' => 'income'],
                    $cat
                );
            }
        }
    }
}
