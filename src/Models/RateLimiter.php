<?php

namespace Naviisml\ApiGuard\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Naviisml\ApiGuard\Database\Factories\RateLimiterFactory;

class RateLimiter extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rate_limits';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'api_key_id',
        'requests',
        'limit',
        'reset_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reset_at' => 'datetime',
    ];

    public function reset(): void
    {
        $this->update([
            'requests' => 0,
        ]);
    }

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }

    protected static function newFactory()
    {
        return RateLimiterFactory::new();
    }
}
