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
use Kenepa\ResourceLock\Models\Concerns\HasLocks;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable implements HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasLocks, LogsActivity, Notifiable, SoftDeletes;

    /**
     * Store original updated_by value when preventing timestamp updates
     */
    protected static $originalUpdatedBy;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'avatar',
        'cover_image',
        'web_app_background_enabled',
        'online_status',
        'username',
        'name',
        'email',
        'google_id',
        'google_avatar_url',
        'microsoft_id',
        'microsoft_avatar_url',
        'timezone',
        'timezone_source',
        'password',
        'api_key',
        'api_key_generated_at',
        'updated_by',
        'latitude',
        'longitude',
        'city',
        'country',
        'location_updated_at',
        'location_source',
        'location_manually_set',
        'timezone_manually_set',
        'last_auto_location_update',
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
                'web_app_background_enabled',
                'online_status',
                'email_verified_at',
                'deleted_at',
                'timezone',
                'timezone_source',
                'api_key_generated_at',
                'city',
                'country',
                'location_updated_at',
                'location_source',
                'location_manually_set',
                'timezone_manually_set',
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
            $trackedFields = ['username', 'name', 'email', 'avatar', 'cover_image', 'web_app_background_enabled', 'online_status', 'email_verified_at', 'deleted_at', 'timezone', 'timezone_source', 'api_key_generated_at', 'city', 'country', 'location_updated_at', 'location_source', 'location_manually_set', 'timezone_manually_set'];

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
            'web_app_background_enabled' => 'boolean',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'location_updated_at' => 'datetime',
            'last_auto_location_update' => 'datetime',
        ];
    }

    protected static function booted()
    {
        static::saving(function ($user) {
            if (empty($user->name)) {
                $user->name = $user->username;
            }
        });

        static::updating(function ($user) {
            // If only online_status is being updated, prevent updated_at and updated_by from changing
            $dirtyFields = $user->getDirty();
            if (count($dirtyFields) === 1 && isset($dirtyFields['online_status'])) {
                $user->timestamps = false;
                // Store the original updated_by value in a static property
                static::$originalUpdatedBy = $user->getOriginal('updated_by');
            }
        });

        static::updated(function ($user) {
            // Re-enable timestamps after the update
            $user->timestamps = true;

            // If we prevented timestamps, restore the original updated_by value
            if (isset(static::$originalUpdatedBy)) {
                // Use raw query to avoid triggering events
                \DB::table('users')
                    ->where('id', $user->id)
                    ->update(['updated_by' => static::$originalUpdatedBy]);
                static::$originalUpdatedBy = null;
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
        // Priority 1: Custom uploaded avatar (NEVER overwritten)
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }

        // Priority 2: Google avatar (CAN be overwritten by custom upload)
        if ($this->google_avatar_url) {
            return $this->google_avatar_url;
        }

        // Priority 3: Microsoft avatar (CAN be overwritten by custom upload)
        if ($this->microsoft_avatar_url) {
            return $this->microsoft_avatar_url;
        }

        // Priority 3: Default Filament avatar (CAN be overwritten by both)
        return null; // Filament handles default avatar generation
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

    /**
     * Update Google avatar URL (only if no custom avatar exists)
     */
    public function updateGoogleAvatar(string $googleAvatarUrl): void
    {
        // Only update Google avatar if no custom avatar exists
        if (! $this->avatar) {
            $this->update(['google_avatar_url' => $googleAvatarUrl]);
        }
        // If custom avatar exists, do nothing (preserve custom avatar)
    }

    /**
     * Check if user has Google authentication linked
     */
    public function hasGoogleAuth(): bool
    {
        return ! empty($this->google_id);
    }

    /**
     * Disconnect Google account from user
     */
    public function disconnectGoogle(): void
    {
        $this->update([
            'google_id' => null,
            'google_avatar_url' => null, // Also clear Google avatar
        ]);
    }

    /**
     * Update Microsoft avatar URL (only if no custom avatar exists)
     */
    public function updateMicrosoftAvatar(string $microsoftAvatarUrl): void
    {
        // Only update Microsoft avatar if no custom avatar exists
        if (! $this->avatar) {
            $this->update(['microsoft_avatar_url' => $microsoftAvatarUrl]);
        }
        // If custom avatar exists, do nothing (preserve custom avatar)
    }

    /**
     * Check if user has Microsoft authentication linked
     */
    public function hasMicrosoftAuth(): bool
    {
        return ! empty($this->microsoft_id);
    }

    /**
     * Disconnect Microsoft account from user
     */
    public function disconnectMicrosoft(): void
    {
        $this->update([
            'microsoft_id' => null,
            'microsoft_avatar_url' => null, // Also clear Microsoft avatar
        ]);
    }

    /**
     * Get the user's chatbot conversations
     */
    public function chatbotConversations(): HasMany
    {
        return $this->hasMany(ChatbotConversation::class);
    }

    /**
     * Get the user's chatbot backups
     */
    public function chatbotBackups(): HasMany
    {
        return $this->hasMany(ChatbotBackup::class);
    }

    /**
     * Get available online status options
     */
    public static function getOnlineStatusOptions(): array
    {
        return [
            'online' => 'Online',
            'away' => 'Away',
            'dnd' => 'Do Not Disturb',
            'invisible' => 'Invisible',
        ];
    }

    /**
     * Get the online status options with icons
     */
    public static function getOnlineStatusOptionsWithIcons(): array
    {
        return [
            'online' => '<div class="flex items-center gap-2"><div class="w-4 h-4 rounded-full bg-teal-500 border-2 border-white dark:border-gray-900"></div><span>Online</span></div>',
            'away' => '<div class="flex items-center gap-2"><div class="w-4 h-4 rounded-full bg-primary-500 border-2 border-white dark:border-gray-900"></div><span>Away</span></div>',
            'dnd' => '<div class="flex items-center gap-2"><div class="w-4 h-4 rounded-full bg-red-500 border-2 border-white dark:border-gray-900"></div><span>Do Not Disturb</span></div>',
            'invisible' => '<div class="flex items-center gap-2"><div class="w-4 h-4 rounded-full bg-gray-400 border-2 border-white dark:border-gray-900"></div><span>Invisible</span></div>',
        ];
    }

    /**
     * Get the color for the online status indicator
     */
    public function getOnlineStatusColor(): string
    {
        return match ($this->online_status) {
            'online' => 'success', // Teal/Green
            'away' => 'primary', // Primary color
            'dnd' => 'danger', // Red
            'invisible' => 'gray', // Gray
            default => 'gray',
        };
    }

    /**
     * Get the display name for the online status
     */
    public function getOnlineStatusDisplayName(): string
    {
        return self::getOnlineStatusOptions()[$this->online_status] ?? 'Unknown';
    }
}
