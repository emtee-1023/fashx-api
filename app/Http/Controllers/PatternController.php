<?php
namespace App\Http\Controllers;

use App\Models\Pattern;
use Illuminate\Http\Request;

class PatternController extends Controller
{
    public function index()
    {
        return Pattern::with(['project', 'mockup'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'mockup_id' => 'nullable|exists:mockups,id',
            'file_path' => 'required|string',
            'format' => 'required|in:pdf,svg,json,dxf',
            'notes' => 'nullable|string',
        ]);
        return Pattern::create($validated);
    }

    public function show(Pattern $pattern)
    {
        return $pattern->load(['project', 'mockup']);
    }

    public function update(Request $request, Pattern $pattern)
    {
        $validated = $request->validate([
            'mockup_id' => 'nullable|exists:mockups,id',
            'file_path' => 'sometimes|required|string',
            'format' => 'sometimes|required|in:pdf,svg,json,dxf',
            'notes' => 'nullable|string',
        ]);
        $pattern->update($validated);
        return $pattern;
    }

    public function destroy(Pattern $pattern)
    {
        $pattern->delete();
        return response()->noContent();
    }
}

