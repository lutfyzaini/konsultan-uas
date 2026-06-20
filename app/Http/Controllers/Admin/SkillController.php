<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    public function index()
    {
        $skills = Skill::latest()->get();

        return view('admin.skills.index', compact('skills'));
    }

    public function create()
    {
        return view('admin.skills.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100'
        ]);

        Skill::create([
            'name' => $request->name
        ]);

        return redirect()
            ->route('admin.skills.index')
            ->with('success', 'Skill berhasil ditambahkan');
    }

    public function edit(Skill $skill)
    {
        return view('admin.skills.edit', compact('skill'));
    }

    public function update(Request $request, Skill $skill)
    {
        $request->validate([
            'name' => 'required|max:100'
        ]);

        $skill->update([
            'name' => $request->name
        ]);

        return redirect()
            ->route('admin.skills.index');
    }

    public function destroy(Skill $skill)
    {
        $skill->delete();

        return back();
    }
}