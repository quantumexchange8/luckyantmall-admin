<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    use HasFactory, Notifiable, InteractsWithMedia, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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

    public function setReferralId(): void
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomString = 'LKMx';

        $length = 10 - strlen($randomString);

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        $this->referral_code = $randomString;
        $this->save();
    }

    public function getChildrenIds(): array
    {
        return User::query()->where('hierarchyList', 'like', '%-' . $this->id . '-%')
            ->pluck('id')
            ->toArray();
    }

    // Relations
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function rank(): BelongsTo
    {
        return $this->belongsTo(GroupRankSetting::class, 'setting_rank_id', 'id');
    }

    public function upline(): BelongsTo
    {
        return $this->belongsTo(User::class, 'upline_id', 'id');
    }

    public function group(): HasOne
    {
        return $this->hasOne(GroupHasUser::class, 'user_id');
    }

    public function delivery_addresses(): HasMany
    {
        return $this->hasMany(DeliveryAddress::class, 'user_id', 'id');
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class, 'user_id', 'id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }

    public function getFirstLeader()
    {
        $first_leader = null;

        $upline = explode("-", substr($this->hierarchyList, 1, -1));
        $count = count($upline) - 1;

        // Check if there are elements in $upline before accessing
        if ($count >= 0) {
            while ($count >= 0) {
                $user = User::find($upline[$count]);
                if (!empty($user->leader_status) && $user->leader_status == 1) {
                    $first_leader = $user;
                    break; // Found the first leader, exit the loop
                }
                $count--;
            }
        }
        return $first_leader;
    }
}
