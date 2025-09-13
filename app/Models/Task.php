<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory;

    /**
     * Konstanta status untuk konsistensi
     */
    const STATUS_TODO = 'To-Do';
    const STATUS_IN_PROGRESS = 'In Progress';
    const STATUS_DONE = 'Done';

    const STATUSES = [
        self::STATUS_TODO,
        self::STATUS_IN_PROGRESS,
        self::STATUS_DONE,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'due_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getDueAtFormattedAttribute(): ?string
    {
        return $this->due_at ? $this->due_at->format('d M Y H:i') : null;
    }

    public function getDueAtInputAttribute(): ?string
    {
        return $this->due_at ? $this->due_at->format('Y-m-d\TH:i') : null;
    }

    public function scopeFilterByStatus($query, $status)
    {
        if (!empty($status) && in_array($status, self::STATUSES)) {
            return $query->where('status', $status);
        }
        return $query;
    }

    public function scopeSortByDueAt($query, $order = 'asc')
    {
        $validOrders = ['asc', 'desc'];
        $order = in_array($order, $validOrders) ? $order : 'asc';
        
        return $query->orderByRaw("due_at IS NULL, due_at {$order}");
    }

    public function scopeUpcoming($query)
    {
        return $query->where('due_at', '>=', now())
                    ->where('due_at', '<=', now()->addDay())
                    ->where('status', '!=', self::STATUS_DONE);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_at', '<', now())
                    ->where('status', '!=', self::STATUS_DONE);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_TODO => 'bg-secondary',
            self::STATUS_IN_PROGRESS => 'bg-warning text-dark',
            self::STATUS_DONE => 'bg-success',
            default => 'bg-light text-dark',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_at && 
               $this->due_at->isPast() && 
               $this->status !== self::STATUS_DONE;
    }

    /**
     * Method helper untuk cek apakah task upcoming (dalam 24 jam)
     */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->due_at && 
               $this->due_at->isFuture() && 
               $this->due_at->lte(now()->addDay()) && 
               $this->status !== self::STATUS_DONE;
    }
}