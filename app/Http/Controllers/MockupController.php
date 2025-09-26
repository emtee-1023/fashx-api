<?php
namespace App\Http\Controllers;

use App\Models\Mockup;
use Illuminate\Http\Request;

class MockupController extends Controller
{
    public function index()
    {
        return Mockup::with(['project', 'sketch', 'patterns'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'sketch_id' => 'nullable|exists:sketches,id',
            'file_path' => 'required|string',
            'prompt_used' => 'nullable|string',
            'model_used' => 'nullable|string',
        ]);
        return Mockup::create($validated);
    }

    public function show(Mockup $mockup)
    {
        return $mockup->load(['project', 'sketch', 'patterns']);
    }

    public function update(Request $request, Mockup $mockup)
    {
        $validated = $request->validate([
            'sketch_id' => 'nullable|exists:sketches,id',
            'file_path' => 'sometimes|required|string',
            'prompt_used' => 'nullable|string',
            'model_used' => 'nullable|string',
        ]);
        $mockup->update($validated);
        return $mockup;
    }

    public function destroy(Mockup $mockup)
    {
        $mockup->delete();
        return response()->noContent();
    }
}

