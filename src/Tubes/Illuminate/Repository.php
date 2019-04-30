<?php namespace Steenbag\Tubes\Illuminate;

class Repository implements \Steenbag\Tubes\Contract\Repository
{

    protected $illuminateRepository;

    public function __construct(\Illuminate\Config\Repository $config)
    {
        $this->illuminateRepository = $config;
    }

    /**
     * Get the specified configuration value.
     *
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->illuminateRepository->get($key, $default);
    }

    /**
     * Set a given configuration value.
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        return $this->illuminateRepository->set($key, $value);
    }
}
