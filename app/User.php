<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Validator;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    protected $primaryKey = 'user_id';
    protected $fillable = [
        'name', 'email', 'password', 'confirmation_token', 'email_verified_at'
    ];
    protected $hidden = [
        'password', 'remember_token', 'confirmation_token', 'pivot'
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
    ];

    protected $appends = ['is_admin'];

    public static $rules = [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique_not_me:users,user_id'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ];

    public static $rulesUpdate = [
        'name' => ['string', 'max:255'],
        'email' => ['string', 'email', 'max:255', 'unique_not_me:users,user_id'],
        'password' => ['string', 'min:8', 'confirmed']
    ];

    public static function validator($data, bool $update = false)
    {
        return Validator::make($data, $update ? self::$rulesUpdate: self::$rules);
    }

    public function rules()
    {
        return $this->belongsToMany(Rule::class, 'rule_users', 'user_id', 'rule_id')->withTimestamps();
    }

    public function getIsAdminAttribute()
    {
        return $this->rules()->where('alias', 'like', '%admin%')->count() > 0;
    }

    public function scopeWithRules($query)
    {
        return $query->with(['rules' => function ($query) {
            $query->select(['rules.rule_id', 'rules.label', 'rules.alias','rule_users.created_at']);
        }]);
    }

}
