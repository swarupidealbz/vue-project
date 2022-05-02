<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notifications extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'notifications';

    const CONTENT = 'content';
    const PRIMARY_TOPICS = 'primary_topic';
    const CHILD_TOPICS = 'child_topic';
}
