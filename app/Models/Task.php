<?php

namespace App\Models;

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

    protected $cast = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }
}
