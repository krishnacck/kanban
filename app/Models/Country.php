<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Country extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'order', 'board_id', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function deleteOrFail(): void
    {
        if ($this->tasks()->exists()) {
            throw ValidationException::withMessages([
                'country' => 'Cannot delete a country that has tasks assigned to it.',
            ]);
        }
        $this->delete();
    }
}
