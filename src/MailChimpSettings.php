<?php
namespace Platon\AR;

use Illuminate\Database\Eloquent\Model;
use ViralStore\Settings;

class MailChimpSettings extends Model
{
    protected $table = 'ar_mailchimp_settings';

    use Settings;
}
