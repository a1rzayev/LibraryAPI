<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'category_id'
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * Get the auto-incrementing key type.
     */
    public function getKeyType()
    {
        return 'string';
    }

    /**
     * Scope for filtering books by title
     */
    public function scopeFilterByTitle(Builder $query, string $title): Builder
    {
        return $query->where('title', 'ILIKE', "%{$title}%");
    }

    /**
     * Scope for filtering books by author
     */
    public function scopeFilterByAuthor(Builder $query, string $author): Builder
    {
        return $query->where('author', 'ILIKE', "%{$author}%");
    }

    /**
     * Scope for filtering books by category
     */
    public function scopeFilterByCategory(Builder $query, string $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope for filtering books by date range
     */
    public function scopeFilterByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for filtering books by multiple criteria
     */
    public function scopeApplyFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['title']) && !empty($filters['title'])) {
            $query->filterByTitle($filters['title']);
        }

        if (isset($filters['author']) && !empty($filters['author'])) {
            $query->filterByAuthor($filters['author']);
        }

        if (isset($filters['category_id']) && !empty($filters['category_id'])) {
            $query->filterByCategory($filters['category_id']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->filterByDateRange($filters['start_date'], $filters['end_date']);
        }

        return $query;
    }

    /**
     * Scope for sorting books
     */
    public function scopeSortBy(Builder $query, string $sortBy = 'created_at', string $sortOrder = 'desc'): Builder
    {
        $allowedSortFields = ['title', 'author', 'created_at', 'updated_at'];
        
        if (in_array($sortBy, $allowedSortFields)) {
            return $query->orderBy($sortBy, $sortOrder);
        }

        return $query->orderBy('created_at', 'desc');
    }
}
