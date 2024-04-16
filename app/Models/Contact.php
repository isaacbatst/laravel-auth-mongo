<?php

namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\EmbedsMany;

class Contact extends Model
{
    protected $collection = 'contacts';
    protected $fillable = ['name', 'phone', 'email'];

    public function messages(): EmbedsMany
    {
        return $this->embedsMany(Message::class);
    }
}
