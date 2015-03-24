<?php
namespace Heonozis\AR;

use Illuminate\Database\Eloquent\Model;

class MailChimpSettings extends Model
{
    protected $table = 'ar_mailchimp_settings';

    protected $fillable = array('key', 'value');

    use Settings;
}
