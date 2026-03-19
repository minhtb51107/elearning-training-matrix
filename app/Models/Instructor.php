<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    protected $fillable = ['user_id', 'name', 'type', 'email', 'phone', 'company'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}