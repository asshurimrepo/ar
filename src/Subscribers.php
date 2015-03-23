<?php

namespace Heonozis\AR;

use Illuminate\Database\Eloquent\Model;

class Subscribers extends Model
{
    protected $table = 'ar_subscribers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'active'];

}
