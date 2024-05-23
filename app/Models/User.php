<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'working_hours_start',
        'working_hours_end',
        'break_hours_start',
        'break_hours_end',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $appends = [
        'working_hours',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }

    public function getWorkingHoursAttribute(): int|float|null
    {
        if (
            (null === $this->working_hours_start || null === $this->working_hours_end) ||
            (null === $this->break_hours_start || null === $this->break_hours_end)
        ) {
            return null;
        }

        $firstPeriodStart = Carbon::createFromTimeString($this->working_hours_start);
        $firstPeriodEnd =  Carbon::createFromTimeString($this->break_hours_start);
        $secondPeriodStart = Carbon::createFromTimeString($this->break_hours_end);
        $secondPeriodEnd =  Carbon::createFromTimeString($this->working_hours_end);

        return $firstPeriodStart->diffInHours($firstPeriodEnd) + $secondPeriodStart->diffInHours($secondPeriodEnd);
    }
}
