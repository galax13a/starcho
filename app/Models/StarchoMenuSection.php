<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StarchoMenuSection extends Model
{
    use HasFactory;

    protected $table = 'starcho_menu_sections';

    protected $fillable = [
        'panel',
        'label',
        'sort_order',
    ];
}
