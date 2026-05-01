<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Status extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color', 'order', 'board_id', 'is_completed'];

    protected $casts = ['is_completed' => 'boolean'];

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
                'status' => 'Cannot delete a status that has tasks assigned to it.',
            ]);
        }
        $this->delete();
    }
}
