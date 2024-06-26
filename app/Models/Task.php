<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterval;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    public const STATUS_CREATED = 'created';
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_CANCELED = 'canceled';

    public const STATUSES = [
        self::STATUS_CREATED => self::STATUS_CREATED,
        self::STATUS_PENDING => self::STATUS_PENDING,
        self::STATUS_CONFIRMED => self::STATUS_CONFIRMED,
        self::STATUS_IN_PROGRESS => self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED => self::STATUS_COMPLETED,
        self::STATUS_PAUSED => self::STATUS_PAUSED,
        self::STATUS_CANCELED => self::STATUS_CANCELED,
    ];

    protected $fillable = [
        'title',
        'notes',
        'duration',
        'status',
        'start_at',
        'end_at',
    ];

    protected $dates = [
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    protected $appends = [
        'duration_for_humans',
    ];

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function scopeDoable(): Builder
    {
        return $this->whereIn('status', [
            self::STATUS_CONFIRMED,
            self::STATUS_IN_PROGRESS,
        ]);
    }

    public function getDurationForHumansAttribute(): string|null
    {
        if (
            (null === $this->duration ||
                (null === $this->assignee->working_hours)
            )
        ) {
            return null;
        }

        CarbonInterval::setCascadeFactors([
            'minute' => [60, 'seconds'],
            'hour' => [60, 'minutes'],
            'day' => [$this->assignee->working_hours, 'hours'],
            'week' => [5, 'days'],
        ]);

        return (new CarbonInterval($this->duration))->cascade()->forHumans();
    }
}
