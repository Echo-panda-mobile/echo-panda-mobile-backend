<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
{
    use HasFactory;

    protected $fillable = ['reportable_type', 'reportable_id', 'user_id', 'reason', 'details', 'status'];

    public function reportable()
    {
        return $this->morphTo();
    }
}
