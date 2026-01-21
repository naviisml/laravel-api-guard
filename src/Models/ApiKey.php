<?php

namespace Naviisml\ApiGuard\Models;

use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Naviisml\ApiGuard\Database\Factories\ApiKeyFactory;
use Naviisml\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * @property string $id
 * @property string $public_key
 * @property string $private_key
 * @property DateTime $revoked_at
 * @property DateTime $created_at
 * @property DateTime $updated_at
 *
 * @mixin Builder
 */
class ApiKey extends Model
{
    use UsesTenantConnection;
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'api_keys';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'public_key',
        'private_key',
        'revoked_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'revoked_at' => 'datetime',
        'public_key' => 'string',
        'private_key' => 'encrypted:string',
    ];

    /**
     * Create a new Eloquent model instance.
     */
    public function __construct(array $attributes = [])
    {
        static::creating(function ($model) {
            if (is_null($model->public_key)) {
                $model->regeneratePublicKey();
            }

            if (is_null($model->private_key)) {
                $model->regeneratePrivateKey();
            }
        });

        parent::__construct($attributes);
    }

    /**
     * Determine if the api key is revoked.
     */
    public function getIsRevokedAttribute(): bool
    {
        return $this->revoked_at && Carbon::parse($this->revoked_at)->isBefore(now());
    }

    /**
     * Regenerate the public key for the api.
     */
    public function regeneratePublicKey(): void
    {
        $this->fill([
            'public_key' => strtoupper(implode('-', str_split(Str::random(30), 6))),
        ]);
    }

    /**
     * Regenerate the private key for the api.
     */
    public function regeneratePrivateKey(): void
    {
        $this->fill([
            'private_key' => base64_encode(random_bytes(32)),
        ]);
    }

    public function ratelimiter(): HasOne
    {
        return $this->hasOne(RateLimiter::class);
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        return ApiKeyFactory::new();
    }
}
