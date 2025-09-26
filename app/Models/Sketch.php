<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sketch extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'file_path', 'original_filename', 'notes'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function mockups()
    {
        return $this->hasMany(Mockup::class);
    }
}

