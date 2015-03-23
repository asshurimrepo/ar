<?php
namespace Heonozis\AR;

use Illuminate\Database\Eloquent\Model;

class AweberSettings extends Model
{
    use Settings;

    protected $table = 'ar_aweber_settings';
}
