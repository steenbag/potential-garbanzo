<?php namespace Steenbag\Tubes\Keys\Ardent;

use LaravelBook\Ardent\Ardent;

class ApiGrant extends Ardent
{

    protected $fillable = ['api_key_id', 'api', 'method', 'value'];

    public function apiKey()
    {
        return $this->belongsTo('Steenbag\Tubes\Keys\Ardent\ApiKey');
    }

}
