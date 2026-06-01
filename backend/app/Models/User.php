<?php

namespace App\Models;
use App\Enums\AccountStatus;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use SoftDeletes, HasUuids;

    protected $table = 'users';

    protected $fillable = [
        'email', 'phone', 'password', 'role', 'status',
        'first_name', 'last_name', 'avatar_url', 'locale',
        'email_verified_at', 'last_login_at',
    ];

    public function getNameAttribute(): ?string
    {
        return $this->first_name ? trim($this->first_name . ' ' . $this->last_name) : null;
    }

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'status' => AccountStatus::class,
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class, 'user_id');
    }

    public function clientProfile(): HasOne
    {
        return $this->hasOne(ClientProfile::class, 'user_id');
    }

    public function freelanceProfile(): HasOne
    {
        return $this->hasOne(FreelanceProfile::class, 'user_id');
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class, 'user_id');
    }

    public function authTokens(): HasMany
    {
        return $this->hasMany(AuthToken::class, 'user_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'client_id');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'freelance_id');
    }

    public function clientContracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'client_id');
    }

    public function freelanceContracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'freelance_id');
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot('last_read_at', 'is_muted')
            ->withTimestamps();
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'user_id');
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }

    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    public function portfolioItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            PortfolioItem::class,
            FreelanceProfile::class,
            'user_id',
            'freelance_profile_id',
            'id',
            'id'
        );
    }

    public function boosts(): HasMany
    {
        return $this->hasMany(Boost::class, 'freelance_profile_id');
    }

    public function adminLogs(): HasMany
    {
        return $this->hasMany(AdminLog::class, 'admin_id');
    }

    public function subscription(): HasOne
    {
        return $this->hasOneThrough(
            FreelanceSubscription::class,
            FreelanceProfile::class,
            'user_id',
            'freelance_profile_id',
            'id',
            'id'
        )->whereIn('status', [\App\Enums\SubscriptionStatus::Active, \App\Enums\SubscriptionStatus::Trial])
            ->latest('freelance_subscriptions.created_at');
    }
}
