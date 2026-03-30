<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'company', 'email', 'phone', 'status', 'notes', 'user_id'];

    const STATUSES = ['lead', 'prospect', 'customer', 'churned'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
