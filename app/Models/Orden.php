<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orden extends Model
{
    protected $fillable = [
        'technician_id',
        'address',
        'comments',
        'status',
    ];
    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
