<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopicFavorite extends Model
{
    use HasFactory;
	
	protected $table = 'topic_favorite';
	protected $guarded = [];
    public $timestamps = false;
	
	public function updatedUser()
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }
}
