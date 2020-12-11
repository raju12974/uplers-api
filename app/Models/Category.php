<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    public function events(){
        return $this->belongsToMany(Event::class, 'event_categories', 'category_id', 'event_id');
    }

    public function event_categories(){
        return $this->hasMany(EventCategory::class);
    }
}
