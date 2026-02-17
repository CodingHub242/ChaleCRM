<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'description',
        'due_date',
        'duration',
        'participants',
        'related_to_type',
        'related_to_id',
        'owner_id',
        'completed',
    ];

    protected $casts = [
        'due_date' => 'date',
        'participants' => 'array',
        'completed' => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
