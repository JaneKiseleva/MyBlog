<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $with = ['category', 'author'];
    /**
     * @var mixed
     */
    private $user_id;

    public function scopeFilter($query, array $filters)
    {
        /** @var Builder $query */
        $query->when($filters['search'] ?? false, fn($query, $search) => $query->where(fn($query) => $query->where('title', 'like', '%' . $search . '%')
            ->orWhere('body', 'like', '%' . $search . '%')
        )
        );

        $query->when($filters['category'] ?? false, fn($query, $category) => $query->whereHas('category', fn($query) => $query->where('slug', $category)
        )
        );

        $query->when($filters['author'] ?? false, fn($query, $author) => $query->whereHas('author', fn($query) => $query->where('username', $author)
        )
        );
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function setThumbnailAttribute($imagePath)
    {
        if ($imagePath == null) {
            return $this->attributes['thumbnail'] = null;
        }
        $image = explode('/', $imagePath);
        return $this->attributes['thumbnail'] = end($image);
    }

    public function getThumbnailPathAttribute()
    {
        $fullImagePath = '/storage/thumbnails/';
        $imageDefault = 'image-default.jpg';

        if ($this->thumbnail === null) {
            return $fullImagePath.$imageDefault;
        }
        return $fullImagePath.$this->thumbnail;
    }

}

