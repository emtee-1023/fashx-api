<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Sketch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SketchController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('view', $project);
        $sketches = $project->sketches()->get();
        return response()->json(['success' => true, 'sketches' => $sketches]);
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|mimes:jpeg,png|max:5120',
        ]);

        $sketches = [];
        foreach ($request->file('files') as $file) {
            $path = $file->store('sketches', 'public');
            $sketch = $project->sketches()->create([
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'notes' => null,
            ]);
            $sketches[] = $sketch;
        }

        return response()->json(['success' => true, 'sketches' => $sketches]);
    }

    public function show(Sketch $sketch)
    {
        $this->authorize('view', $sketch);
        $sketch->load(['project', 'mockups']);
        return response()->json(['success' => true, 'sketch' => $sketch]);
    }

    public function update(Request $request, Sketch $sketch)
    {
        $this->authorize('update', $sketch);
        $validated = $request->validate([
            'file_path' => 'sometimes|required|string',
            'original_filename' => 'sometimes|required|string',
            'notes' => 'nullable|string',
        ]);
        $sketch->update($validated);
        return response()->json(['success' => true, 'sketch' => $sketch]);
    }

    public function destroy(Sketch $sketch)
    {
        $this->authorize('delete', $sketch);
        $sketch->delete();
        return response()->json(['success' => true]);
    }
}
