<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Groups extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'groups';
    protected $guarded = [];

    public function topics()
    {
        return $this->belongsToMany(Topics::class ,TopicGroup::class, 'group_id','topic_id');
    }
}
