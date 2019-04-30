<?php namespace Steenbag\Tubes\Keys\Ardent;

use LaravelBook\Ardent\Ardent;

class ApiReferrer extends Ardent
{

    protected $fillable = ['api_key_id', 'type', 'value'];

    public function apiKey()
    {
        return $this->belongsTo('Steenbag\Tubes\Keys\Ardent\ApiKey');
    }

}
