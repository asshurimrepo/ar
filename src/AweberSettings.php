<?php
namespace Platon\AR;

use Illuminate\Database\Eloquent\Model;
use ViralStore\Settings;

class AweberSettings extends Model
{
    use Settings;

    protected $table = 'ar_aweber_settings';
}