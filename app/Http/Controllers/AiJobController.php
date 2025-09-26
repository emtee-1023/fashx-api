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

    /**
     * Show job status and result for a given AiJob.
     */
    public function show(AiJob $aiJob)
    {
        $aiJob->load(['user', 'project', 'sketch', 'mockup', 'pattern']);
        $response = [
            'id' => $aiJob->id,
            'type' => $aiJob->type,
            'status' => $aiJob->status,
            'prompt' => $aiJob->prompt,
            'error_message' => $aiJob->error_message,
            'created_at' => $aiJob->created_at,
            'updated_at' => $aiJob->updated_at,
            'sketch' => $aiJob->sketch,
            'mockup' => $aiJob->mockup,
        ];
        return response()->json(['success' => true, 'job' => $response]);
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

    /**
     * Generate mockups for one or more sketches in a project.
     * Request: { project_id, sketch_ids: [], prompt }
     */
    public function generateMockups(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'sketch_ids' => 'required|array',
            'sketch_ids.*' => 'exists:sketches,id',
            'prompt' => 'required|string',
        ]);

        $userId = $request->user()->id;
        $jobs = [];
        foreach ($validated['sketch_ids'] as $sketchId) {
            $aiJob = \App\Models\AiJob::create([
                'user_id' => $userId,
                'project_id' => $validated['project_id'],
                'sketch_id' => $sketchId,
                'type' => 'mockup_generation',
                'status' => 'pending',
                'prompt' => $validated['prompt'],
            ]);
            \App\Jobs\GenerateMockupJob::dispatch($aiJob);
            $jobs[] = $aiJob;
        }
        return response()->json([
            'success' => true,
            'jobs' => $jobs,
        ]);
    }
}
