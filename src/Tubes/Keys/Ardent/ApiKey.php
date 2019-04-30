<?php namespace Steenbag\Tubes\Keys\Ardent;

use LaravelBook\Ardent\Ardent;

class ApiKey extends Ardent implements \Steenbag\Tubes\Contract\ApiKey
{

    protected $fillable = ['client_name', 'notes', 'slug', 'type', 'api_key', 'password', 'active', 'valid_from', 'valid_until'];

    protected $appends = ['type_readable', 'valid_date_range', 'valid_dates'];

    /**
     * Encrypt our password on update.
     *
     * @param $update
     * @return mixed
     */
    public function beforeSave($update)
    {
        if ($update->isDirty('password')) {
            $update->password = \Crypt::encrypt($update->password);
        }

        return $update;
    }

    public function getDates()
    {
        $dates = parent::getDates();
        if (isset($this->attributes['valid_from'])) {
            $dates []= 'valid_from';
        }
        if (isset($this->attributes['valid_until'])) {
            $dates []= 'valid_until';
        }

        return $dates;
    }

    public function grants()
    {
        return $this->hasMany('Steenbag\Tubes\Keys\Ardent\ApiGrant');
    }

    public function validReferrers()
    {
        return $this->hasMany('Steenbag\Tubes\Keys\Ardent\ApiReferrer');
    }


    /**
     * Get a human readable type attribute.
     */
    public function getTypeReadableAttribute()
    {
        return ucwords($this->type);
    }

    /**
     * Return a string representing the period of time the key is valid.
     *
     * @return string
     */
    public function getValidDateRangeAttribute()
    {
        if (isset($this->valid_from) && isset($this->valid_until)) {
            return $this->valid_from->toDateString() . ' &mdash; ' . $this->valid_until->toDateString();
        } elseif (isset($this->valid_from)) {
            return 'Valid starting on ' . $this->valid_from->toDateString();
        } elseif (isset($this->valid_until)) {
            return 'Valid until ' . $this->valid_until->toDateString();
        }
        return 'Not limited';
    }

    /**
     * Return a JS-readable array of the dates for this API Key.
     *
     * @return array
     */
    public function getValidDatesAttribute()
    {
        return [
            'start' => isset($this->valid_from) ? $this->valid_from->toDateTimeString() : null,
            'end' => isset($this->valid_until) ? $this->valid_until->toDateTimeString() : null,
        ];
    }

    /**
     * Add a query scope to query for active keys.
     *
     * @param $query
     * @param bool|true $active
     */
    public function scopeActive($query, $active = true)
    {
        return $query->where('active', $active);
    }

    /**
     * Return all API Keys with no validity dates, or where the current date falls in the range.
     *
     * @param $query
     * @param null $date
     */
    public function scopeValidDates($query, $date = null)
    {
        $date = $date ?: \Carbon::now();

        return $query->where(function($q) use ($date) {
            $q->where(function ($q) use ($date) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $date);
            })->where(function($q) use ($date) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', $date);
            });
        });
    }

    /**
     * Return all API Keys with no validity dates, or where the current date falls in the range, and the key is active.
     *
     * @param $query
     * @param null $date
     */
    public function scopeValid($query, $date = null)
    {
        $query = $this->scopeActive($query);

        return $this->scopeValidDates($query);
    }

    /**
     * Returns true if the API Key is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool) $this->active;
    }

    /**
     * Returns true if the API Key is valid.
     *
     * @return boolean
     */
    public function isValid()
    {
        if (! $this->isActive()) {
            return false;
        }

        $now = \Carbon::now();

        if (isset($this->valid_from) && $now->lt($this->valid_from)) {
            return false;
        }

        if (isset($this->valid_until) && $now->gt($this->valid_until)) {
            return false;
        }

        return true;
    }

    /**
     * Return all of the valid grants for this API Key.
     *
     * @return array
     */
    public function getGrants()
    {
        return $this->grants->all();
    }

    /**
     * Return all of the valid referrers for this API Key.
     *
     * @return array
     */
    public function getValidReferrers()
    {
        return $this->valid_referrers->all();
    }

    /**
     * Returns true if the passed-in grants is valid.
     *
     * @param $api
     * @param $method
     * @return mixed
     */
    public function isValidGrant($api, $method)
    {
        return $this->grants()->where('api', $api)->whereIn('method', ['*', $method, camel_case($method)])->where('value', 1)->count() > 0;
    }

    /**
     * Return the password for the key.
     *
     * @return string
     */
    public function getPassword()
    {
        return \Crypt::decrypt($this->password);
    }
}
