<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Rule extends Model
{
    protected $perPage = 10;
    protected $primaryKey = 'rule_id';
    protected $fillable = ['label', 'alias'];
    protected $hidden = ['pivot'];
    public static $requiredAliases = ['site_admin', 'default'];
    protected static $rules = [
        'label' => 'required|string|max:50|unique_not_me:rules,rule_id',
        'alias' => 'required|string|max:50|unique_not_me:rules,rule_id',
    ];

    public static function validator($data)
    {
        return Validator::make($data, self::$rules);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'rule_users', 'rule_id', 'user_id')->withTimestamps();
    }

    public function scopeWithUsers($query)
    {
        return $query->with(['users' => function ($query) {
            $query->select(['users.user_id', 'users.email','rule_users.created_at']);
        }]);
    }
}
