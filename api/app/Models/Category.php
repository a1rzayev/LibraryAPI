<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    use HasFactory;
    protected $fillable = ['name'];
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function books()
    {
        return $this->hasMany(Book::class);
    }

    /**
     * Scope for filtering categories by name
     */
    public function scopeFilterByName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'ILIKE', "%{$name}%");
    }

    /**
     * Scope for filtering categories by date range
     */
    public function scopeFilterByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for filtering categories by multiple criteria
     */
    public function scopeApplyFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['name']) && !empty($filters['name'])) {
            $query->filterByName($filters['name']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->filterByDateRange($filters['start_date'], $filters['end_date']);
        }

        return $query;
    }

    /**
     * Scope for sorting categories
     */
    public function scopeSortBy(Builder $query, string $sortBy = 'created_at', string $sortOrder = 'desc'): Builder
    {
        $allowedSortFields = ['name', 'created_at', 'updated_at'];
        
        if (in_array($sortBy, $allowedSortFields)) {
            return $query->orderBy($sortBy, $sortOrder);
        }

        return $query->orderBy('created_at', 'desc');
    }
}
