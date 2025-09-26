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
        return $sketch->load(['project', 'mockups']);
    }

    public function update(Request $request, Sketch $sketch)
    {
        $validated = $request->validate([
            'file_path' => 'sometimes|required|string',
            'original_filename' => 'sometimes|required|string',
            'notes' => 'nullable|string',
        ]);
        $sketch->update($validated);
        return $sketch;
    }

    public function destroy(Sketch $sketch)
    {
        $sketch->delete();
        return response()->noContent();
    }
}
