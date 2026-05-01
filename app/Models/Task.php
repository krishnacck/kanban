<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'priority',
        'due_date',
        'position',
        'status_id',
        'country_id',
        'created_by',
        'assigned_to',
        'board_id',
    ];

    protected $casts = [
        'due_date' => 'date',
        'position' => 'integer',
    ];

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignEndPosition(): void
    {
        $max = static::where('status_id', $this->status_id)
            ->where('country_id', $this->country_id)
            ->max('position');

        $this->position = $max === null ? 0 : $max + 1;
    }

    public static function reindexCell(int $statusId, int $countryId): void
    {
        $tasks = static::where('status_id', $statusId)
            ->where('country_id', $countryId)
            ->orderBy('position')
            ->get();

        foreach ($tasks as $index => $task) {
            if ($task->position !== $index) {
                $task->timestamps = false;
                $task->position = $index;
                $task->save();
            }
        }
    }
}
