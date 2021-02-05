<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use MoinDhattiwala\CountryStateCity\Models\City;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
        'city_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['avtar', 'registered_at', 'last_updated_at'];

    public function getAvtarAttribute()
    {
        if ($this->photo == "") {
            return "https://ui-avatars.com/api/?background=random&rounded=true&size=75&name=" . $this->name;
        }
        return asset('images/users/' . $this->photo);
    }

    public function getRegisteredAtAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getLastUpdatedAtAttribute()
    {
        return $this->updated_at->diffForHumans();
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function state()
    {
        return $this->city->state;
    }

    public function country()
    {
        return $this->state()->country;
    }

}
