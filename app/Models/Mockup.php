<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mockup extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'sketch_id', 'file_path', 'prompt_used', 'model_used'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function sketch()
    {
        return $this->belongsTo(Sketch::class);
    }

    public function patterns()
    {
        return $this->hasMany(Pattern::class);
    }
}

