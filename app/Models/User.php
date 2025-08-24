<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Filament\Models\Contracts\HasAvatar;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, LogsActivity, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'avatar',
        'username',
        'name',
        'email',
        'password',
        'updated_by',
    ];

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['username', 'name', 'email', 'created_at', 'updated_by'])
            ->useLogName('Users');
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
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar ? Storage::url($this->avatar) : null;
    }

    // Abbreviated name: "Amirul Shafiq Harun" => "Amirul S. H."
    public function getShortNameAttribute(): string
    {
        $full = trim($this->attributes['name'] ?? '') ?: trim($this->username ?? '');
        if ($full === '')
            return 'Unknown';
        $parts = preg_split('/\s+/', $full, -1, PREG_SPLIT_NO_EMPTY);
        if (count($parts) === 1)
            return $parts[0];
        $first = array_shift($parts);
        $initials = array_map(function ($p) {
            $ch = mb_substr($p, 0, 1);
            return mb_strtoupper($ch) . '.';
        }, $parts);
        return $first . ' ' . implode(' ', $initials);
    }
}
