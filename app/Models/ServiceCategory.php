<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class ServiceCategory extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'name',
        'status',
        'show_in_reservation',
        'show_in_pos',
        'display_order',
    ];

    public $translatable = ['name'];

    protected $casts = [
        'status' => 'boolean',
        'show_in_reservation' => 'boolean',
        'show_in_pos' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Get the users that have access to this service category.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'service_category_user');
    }
}
