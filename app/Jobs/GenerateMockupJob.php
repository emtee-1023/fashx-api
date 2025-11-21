<?php

namespace App\Jobs;

use App\Models\AiJob;
use App\Models\Mockup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GenerateMockupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $aiJob;

    public function __construct(AiJob $aiJob)
    {
        $this->aiJob = $aiJob;
    }

    public function handle()
    {
        try {
            $this->aiJob->status = 'running';
            $this->aiJob->error_message = null;
            $this->aiJob->save();

            $sketch = $this->aiJob->sketch;
            if (!$sketch) {
                $this->aiJob->status = 'failed';
                $this->aiJob->error_message = 'Sketch not found.';
                $this->aiJob->save();
                Log::error('Sketch not found for AiJob ID: ' . $this->aiJob->id);
                return;
            }

            // Easily switch between ngrok and default domain
            $imageDomain = env('IMAGE_DOMAIN', config('app.url'));
            $sketchUrl = rtrim($imageDomain, '/') . '/storage/' . ltrim($sketch->file_path, '/');
            $apiToken = env('REPLICATE_API_TOKEN');
            $endpoint = 'https://api.replicate.com/v1/models/google/nano-banana/predictions';
            $prompt = $this->aiJob->prompt ?: 'Make the sheets in the style of the logo. Make the scene natural.';
            $imageInputs = [$sketchUrl];
            $response = Http::withToken($apiToken)
                ->acceptJson()
                ->withOptions(['verify' => false])
                ->post($endpoint, [
                    'input' => [
                        'prompt' => $prompt,
                        'image_input' => $imageInputs,
                        'output_format' => 'jpg',
                    ],
                ]);

            if (!$response->successful()) {
                $this->aiJob->status = 'failed';
                $this->aiJob->error_message = 'Replicate API request failed: ' . $response->body();
                $this->aiJob->save();
                Log::error('Replicate API request failed', ['response' => $response->body()]);
                return;
            }

            $predictionId = $response->json()['id'] ?? null;
            if (!$predictionId) {
                $this->aiJob->status = 'failed';
                $this->aiJob->error_message = 'No prediction ID returned from Replicate API.';
                $this->aiJob->save();
                Log::error('No prediction ID returned from Replicate API', ['response' => $response->json()]);
                return;
            }

            $pollEndpoint = $endpoint . '/' . $predictionId;
            $status = null;
            $outputUrl = null;
            for ($i = 0; $i < 60; $i++) { // max 5 minutes
                sleep(5);
                $poll = Http::withToken($apiToken)
                    ->acceptJson()
                    ->withOptions(['verify' => false])
                    ->get($pollEndpoint);
                if (!$poll->successful()) {
                    continue;
                }
                $pollData = $poll->json();
                $status = $pollData['status'] ?? null;
                if ($status === 'succeeded') {
                    $outputUrl = $pollData['output'][0] ?? null;
                    break;
                } elseif ($status === 'failed') {
                    break;
                }
            }

            if ($status === 'succeeded' && $outputUrl) {
                $imageResponse = Http::get($outputUrl);
                if (!$imageResponse->successful()) {
                    $this->aiJob->status = 'failed';
                    $this->aiJob->error_message = 'Failed to download mockup image.';
                    $this->aiJob->save();
                    Log::error('Failed to download mockup image', ['url' => $outputUrl]);
                    return;
                }
                $imageContents = $imageResponse->body();
                $filename = 'mockups/' . uniqid('mockup_') . '.png';
                Storage::disk('public')->put($filename, $imageContents);
                Mockup::create([
                    'project_id' => $sketch->project_id,
                    'sketch_id' => $sketch->id,
                    'image_path' => $filename,
                    'prompt_used' => $prompt,
                    'model_used' => 'google/nano-banana',
                ]);
                $this->aiJob->status = 'completed';
                $this->aiJob->save();
            } else {
                $this->aiJob->status = 'failed';
                $this->aiJob->error_message = 'Replicate prediction failed or timed out.';
                $this->aiJob->save();
                Log::error('Replicate prediction failed or timed out', ['status' => $status, 'job_id' => $this->aiJob->id]);
            }
        } catch (\Exception $e) {
            $this->aiJob->status = 'failed';
            $this->aiJob->error_message = $e->getMessage();
            $this->aiJob->save();
            Log::error('GenerateMockupJob exception', ['error' => $e->getMessage(), 'job_id' => $this->aiJob->id]);
        }
    }
}
