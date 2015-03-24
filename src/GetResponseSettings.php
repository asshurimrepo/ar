<?php

namespace Heonozis\AR;

use Illuminate\Database\Eloquent\Model;

class GetResponseSettings extends Model
{
    use Settings;

    protected $table = 'ar_getresponse_settings';

    protected $fillable = array('key', 'value');

}
