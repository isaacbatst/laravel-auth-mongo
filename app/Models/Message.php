<?php

namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['phone', 'text', 'location', 'created_at'];
}