<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Delete a category.
     */
    public function destroy(Request $request, Category $category)
    {
        $family = $request->user()->family;

        if ($category->family_id !== $family->id) {
            abort(403);
        }

        // Dissolve categories or delete category safely
        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus!');
    }
}
