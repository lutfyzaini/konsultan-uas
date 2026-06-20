<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100'
        ]);

        Category::create([
            'name' => $request->name
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success','Kategori berhasil ditambahkan');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|max:100'
        ]);

        $category->update([
            'name' => $request->name
        ]);

        return redirect()
            ->route('admin.categories.index');
    }

    public function destroy(Category $category)
    {
        $isUsed = \App\Models\ExpertProfile::where('category_id', $category->id)->exists();

        if ($isUsed) {
            return back()->with('error', 'Kategori gagal dihapus karena masih digunakan oleh Pakar aktif.');
        }

        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}