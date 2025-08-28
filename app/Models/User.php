<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable implements HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, LogsActivity, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'avatar',
        'cover_image',
        'username',
        'name',
        'email',
        'password',
        'api_key',
        'api_key_generated_at',
        'updated_by',
    ];

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'username',
                'name',
                'email',
                'avatar',
                'cover_image',
                'email_verified_at',
                'deleted_at',
            ])
            ->logOnlyDirty() // Only log when values actually change
            ->useLogName('Users');
    }

    /**
     * Prevent logging when no meaningful changes occur
     */
    public function shouldLogEvent(string $eventName): bool
    {
        // Don't log updates if no tracked fields actually changed
        if ($eventName === 'updated') {
            $dirtyFields = $this->getDirty();
            $trackedFields = ['username', 'name', 'email', 'avatar', 'cover_image', 'email_verified_at', 'deleted_at'];

            // Check if any tracked fields actually changed
            $trackedFieldsChanged = ! empty(array_intersect(array_keys($dirtyFields), $trackedFields));

            if (! $trackedFieldsChanged) {
                return false;
            }
        }

        return true;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted()
    {
        static::saving(function ($user) {
            if (empty($user->name)) {
                $user->name = $user->username;
            }
        });

    }

    public function getNameAttribute($value): string
    {
        return $value ?? $this->username ?? 'name';
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Relations for common user-owned resources
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function phoneNumbers(): HasMany
    {
        return $this->hasMany(PhoneNumber::class);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar ? Storage::url($this->avatar) : null;
    }

    public function getFilamentCoverImageUrl(): ?string
    {
        return $this->cover_image ? Storage::url($this->cover_image) : null;
    }

    // Abbreviated name: "Amirul Shafiq Harun" => "Amirul S. H."
    public function getShortNameAttribute(): string
    {
        $full = trim($this->attributes['name'] ?? '') ?: trim($this->username ?? '');
        if ($full === '') {
            return 'Unknown';
        }
        $parts = preg_split('/\s+/', $full, -1, PREG_SPLIT_NO_EMPTY);
        if (count($parts) === 1) {
            return $parts[0];
        }
        $first = array_shift($parts);
        $initials = array_map(function ($p) {
            $ch = mb_substr($p, 0, 1);

            return mb_strtoupper($ch).'.';
        }, $parts);

        return $first.' '.implode(' ', $initials);
    }

    /**
     * Generate a new API key for the user
     */
    public function generateApiKey(): string
    {
        // $apiKey = 'ak_' . bin2hex(random_bytes(32));
        $apiKey = 'cheqqme_'.bin2hex(random_bytes(32));

        $this->update([
            'api_key' => $apiKey,
            'api_key_generated_at' => now(),
        ]);

        return $apiKey;
    }

    /**
     * Check if the user has a valid API key
     */
    public function hasApiKey(): bool
    {
        return ! empty($this->api_key);
    }

    /**
     * Get the masked API key for display (shows first 8 and last 4 characters)
     */
    public function getMaskedApiKey(): ?string
    {
        if (! $this->api_key) {
            return null;
        }

        $prefix = substr($this->api_key, 0, 8);
        $suffix = substr($this->api_key, -4);

        return $prefix.'****************************'.$suffix;
    }

    /**
     * Validate API key against user's stored key
     */
    public function validateApiKey(string $apiKey): bool
    {
        return $this->api_key === $apiKey;
    }
}
