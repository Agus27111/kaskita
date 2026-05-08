<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Kategori default sistem (family_id = null).
     * Semua keluarga bisa menggunakan kategori ini.
     */
    public function run(): void
    {
        $categories = [
            // === EXPENSE ===
            ['name' => 'Makan & Minum', 'type' => 'expense', 'icon' => 'utensils', 'color' => '#ef4444'],
            ['name' => 'Transport', 'type' => 'expense', 'icon' => 'car', 'color' => '#f97316'],
            ['name' => 'Belanja', 'type' => 'expense', 'icon' => 'shopping-bag', 'color' => '#eab308'],
            ['name' => 'Tagihan', 'type' => 'expense', 'icon' => 'receipt', 'color' => '#8b5cf6'],
            ['name' => 'Kesehatan', 'type' => 'expense', 'icon' => 'heart-pulse', 'color' => '#ec4899'],
            ['name' => 'Pendidikan', 'type' => 'expense', 'icon' => 'graduation-cap', 'color' => '#3b82f6'],
            ['name' => 'Hiburan', 'type' => 'expense', 'icon' => 'gamepad-2', 'color' => '#14b8a6'],
            ['name' => 'Sedekah/Infaq', 'type' => 'expense', 'icon' => 'hand-heart', 'color' => '#22c55e'],
            ['name' => 'Lainnya', 'type' => 'expense', 'icon' => 'ellipsis', 'color' => '#6b7280'],

            // === INCOME ===
            ['name' => 'Gaji', 'type' => 'income', 'icon' => 'wallet', 'color' => '#10b981'],
            ['name' => 'Freelance', 'type' => 'income', 'icon' => 'laptop', 'color' => '#06b6d4'],
            ['name' => 'Investasi', 'type' => 'income', 'icon' => 'trending-up', 'color' => '#8b5cf6'],
            ['name' => 'Hadiah', 'type' => 'income', 'icon' => 'gift', 'color' => '#f43f5e'],
            ['name' => 'Bonus', 'type' => 'income', 'icon' => 'star', 'color' => '#eab308'],
            ['name' => 'Lainnya', 'type' => 'income', 'icon' => 'ellipsis', 'color' => '#6b7280'],
        ];

        foreach ($categories as $category) {
            Category::withoutGlobalScopes()->updateOrCreate(
                ['name' => $category['name'], 'type' => $category['type'], 'family_id' => null],
                $category
            );
        }
    }
}
