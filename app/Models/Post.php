<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Post extends BaseModel
{
    use SoftDeletes, HasFactory;

    protected $table = 'posts';

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'image',
        'status',
        'published_at',
        'is_featured',
    ];

    /**
     * Define searchable columns for posts.
     *
     * @var array
     */
    protected $searchables = ['title', 'content', 'excerpt'];

    /**
     * Override getSearchables() to return the defined columns.
     */
    public static function getSearchables(): array
    {
        return ['title', 'content', 'excerpt'];
    }

    /**
     * Define storage path for this model's files.
     */
    public function getStoragePath(): string
    {
        return 'posts';
    }

    /**
     * Define file upload fields.
     */
    public function getFileFields(): array
    {
        return ['image'];
    }

    /**
     * Get the full public URL of the image.
     */
    public function getImageUrl(): ?string
    {
        return $this->image ? url('/') . Storage::url($this->image) : null;
    }

    /**
     * Get the user that owns the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
