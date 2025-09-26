<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pattern extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'mockup_id', 'file_path', 'format', 'notes'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function mockup()
    {
        return $this->belongsTo(Mockup::class);
    }
}

