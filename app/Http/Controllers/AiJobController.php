<?php

namespace App\Http\Controllers;

use App\Models\AiJob;
use Illuminate\Http\Request;


class AiJobController extends Controller
{
    public function index()
    {
        return AiJob::with(['user', 'project', 'sketch', 'mockup', 'pattern'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
            'sketch_id' => 'nullable|exists:sketches,id',
            'mockup_id' => 'nullable|exists:mockups,id',
            'pattern_id' => 'nullable|exists:patterns,id',
            'type' => 'required|in:sketch_to_mockup,mockup_to_pattern,other',
            'status' => 'required|in:pending,running,completed,failed',
            'prompt' => 'nullable|string',
            'model_used' => 'nullable|string',
            'error_message' => 'nullable|string',
            'result_file_path' => 'nullable|string',
        ]);
        return AiJob::create($validated);
    }

    public function show(AiJob $aiJob)
    {
        return $aiJob->load(['user', 'project', 'sketch', 'mockup', 'pattern']);
    }

    public function update(Request $request, AiJob $aiJob)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'sketch_id' => 'nullable|exists:sketches,id',
            'mockup_id' => 'nullable|exists:mockups,id',
            'pattern_id' => 'nullable|exists:patterns,id',
            'type' => 'sometimes|required|in:sketch_to_mockup,mockup_to_pattern,other',
            'status' => 'sometimes|required|in:pending,running,completed,failed',
            'prompt' => 'nullable|string',
            'model_used' => 'nullable|string',
            'error_message' => 'nullable|string',
            'result_file_path' => 'nullable|string',
        ]);
        $aiJob->update($validated);
        return $aiJob;
    }

    public function destroy(AiJob $aiJob)
    {
        $aiJob->delete();
        return response()->noContent();
    }
}
