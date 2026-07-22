<?php

namespace App\Modules\News\Models;

use App\Modules\Category\Models\Category;
use Database\Factories\NewsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * News article.
 *
 * @property int $id
 * @property int $category_id
 * @property string $title
 * @property string $slug
 * @property string $summary
 * @property string $content
 * @property string|null $image_url
 * @property string $author
 * @property Carbon|null $published_at
 * @property bool $is_featured
 * @property-read Category $category
 */
class News extends Model
{
    /** @use HasFactory<NewsFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'summary',
        'content',
        'image_url',
        'author',
        'published_at',
        'is_featured',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    /** Parent category. */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected static function newFactory(): NewsFactory
    {
        return NewsFactory::new();
    }
}
