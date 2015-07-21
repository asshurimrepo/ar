<?php namespace Heonozis\AR;

use Illuminate\Database\Eloquent\Model;

class HtmlInputs extends Model
{
    protected $table = 'ar_html_inputs';

    protected $fillable = array('name', 'type', 'value');


}
