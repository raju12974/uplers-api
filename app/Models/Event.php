<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    public function location(){
        return $this->belongsTo(Location::class);
    }

    public function event_categories(){
        return $this->hasMany(EventCategory::class);
    }

    public function categories(){
        return $this->belongsToMany(Category::class, 'event_categories', 'event_id', 'category_id');
    }

    public function comments(){
        return $this->hasMany(Comment::class)->orderByDesc('id');
    }
}
