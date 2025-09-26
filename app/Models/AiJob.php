<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'sketch_id',
        'mockup_id',
        'pattern_id',
        'type',
        'status',
        'prompt',
        'model_used',
        'error_message',
        'result_file_path'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function sketch()
    {
        return $this->belongsTo(Sketch::class);
    }

    public function mockup()
    {
        return $this->belongsTo(Mockup::class);
    }

    public function pattern()
    {
        return $this->belongsTo(Pattern::class);
    }
}
