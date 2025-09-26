<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sketches()
    {
        return $this->hasMany(Sketch::class);
    }

    public function mockups()
    {
        return $this->hasMany(Mockup::class);
    }

    public function patterns()
    {
        return $this->hasMany(Pattern::class);
    }
}
