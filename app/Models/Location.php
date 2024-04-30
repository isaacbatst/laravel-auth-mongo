<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Location extends Model
{
  protected $fillable = ['lat', 'lng', 'created_at'];
  protected $collection = 'locations';
}
