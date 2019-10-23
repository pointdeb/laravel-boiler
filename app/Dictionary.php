<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Dictionary extends Model
{
    protected $perPage = 10;
    protected $primaryKey = 'dictionary_id';
    protected $fillable = ['key', 'value', 'locale'];

    protected static $rules = [
        'key' => 'required|string|max:50',
        'value' => 'required|string|max:50',
        'locale' => 'required|string|max:3',
    ];

    public static function validator($data)
    {
        return Validator::make($data, self::$rules);
    }
}
