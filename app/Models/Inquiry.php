<?php

namespace App\Models;

use Database\Factories\InquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'email',
    'company',
    'project_type',
    'message',
    'status',
    'ip_address',
    'user_agent',
    'contacted_at',
])]
class Inquiry extends Model
{
    /** @use HasFactory<InquiryFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'contacted_at' => 'datetime',
        ];
    }

    /**
     * Scope a query to the default inquiry ordering.
     */
    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByRaw("
            case
                when status = 'new' then 0
                when status = 'reviewed' then 1
                when status = 'contacted' then 2
                else 3
            end
        ")->latest();
    }
}
