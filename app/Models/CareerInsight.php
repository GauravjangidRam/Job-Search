<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareerInsight extends Model
{
    protected $fillable = [
        'type',
        'label',
        'value',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }
}
