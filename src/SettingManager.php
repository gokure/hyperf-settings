<?php

declare(strict_types=1);

namespace Gokure\Settings;

use Hyperf\Contract\ConfigInterface;
use InvalidArgumentException;

class SettingManager
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * The array of created "drivers".
     *
     * @var array
     */
    protected $drivers = [];

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function getDriver($name = 'default')
    {
        if (isset($this->drivers[$name]) && $this->drivers[$name] instanceof Store) {
            return $this->drivers[$name];
        }

        $config = $this->config->get("settings.$name");
        if (empty($config)) {
            throw new InvalidArgumentException(sprintf('The settings config %s is invalid.', $name));
        }

        $class = $config['driver'] ?? FileSystemStore::class;

        $instance = make($class, ['config' => $config]);

        return $this->drivers[$name] = $instance;
    }

    public function call($callback, $key, $name = 'default')
    {
        $driver = $this->getDriver($name);

        if ($driver->has($key)) {
            return $driver->get($key);
        }

        $result = call($callback);
        $driver->set($key, $result);

        return $result;
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->getDriver()->$method(...$parameters);
    }
}
