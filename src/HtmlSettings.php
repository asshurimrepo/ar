<?php namespace Heonozis\AR;

use Heonozis\AR\Settings;
use Illuminate\Database\Eloquent\Model;

class HtmlSettings extends Model
{
    protected $table = 'ar_html_settings';

    protected $fillable = array('key', 'value');

    use Settings;

}
