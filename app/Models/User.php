<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Restaurant;
use App\Models\Order;
use App\Models\Address;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected static function boot()
    {
        parent::boot();

        static::creating(function (User $user) {
            if (!$user->referral_code) {
                do {
                    $code = strtoupper(\Illuminate\Support\Str::random(6));
                } while (static::where('referral_code', $code)->exists());

                $user->referral_code = $code;
            }
        });
    }

    // ✅ Attribute style hatao, yeh use karo
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'is_online',
        'loyalty_points',
        'wallet_balance',
        'referral_code',
        'referred_by',
        'referral_bonus_paid',
        'profile_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ─── Relationships ───────────────────────────────
    public function restaurants()
    {
        return $this->hasMany(Restaurant::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteRestaurants()
    {
        return $this->belongsToMany(Restaurant::class, 'favorites')->withTimestamps();
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class)->latest();
    }

    public function disputes()
    {
        return $this->hasMany(OrderDispute::class);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referredUsers()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    // Profile photo — works whether it holds a local storage path or a full URL.
    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (!$this->profile_photo) return null;
        return str_starts_with($this->profile_photo, 'http')
            ? $this->profile_photo
            : asset('storage/' . $this->profile_photo);
    }

    // ─── Scopes ──────────────────────────────────────
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    // ─── Role Helpers ────────────────────────────────
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isRestaurantOwner(): bool
    {
        return $this->role === 'restaurant_owner';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function isDeliveryAgent(): bool
    {
        return $this->role === 'delivery_agent';
    }

    // ─── Role-based redirect path ─────────────────────
    public function dashboardRouteName(): string
    {
        return match ($this->role) {
            'admin'            => 'admin.dashboard',
            'restaurant_owner' => 'owner.dashboard',
            'delivery_agent'   => 'delivery.dashboard',
            default            => 'customer.dashboard',
        };
    }

    // ─── Casts ───────────────────────────────────────
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'role'              => 'string',
            'is_online'         => 'boolean',
        ];
    }
}