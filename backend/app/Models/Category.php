<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * News category.
 * Used for grouping articles and building the menu.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $sort_order
 * @property bool $show_in_menu
 */
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    // Columns returned by category API and eager load
    public const API_COLUMNS = ['id', 'name', 'slug', 'sort_order'];

    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'show_in_menu',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'show_in_menu' => 'boolean',
        ];
    }

    /** Articles in this category. */
    public function news(): HasMany
    {
        return $this->hasMany(News::class);
    }
}
