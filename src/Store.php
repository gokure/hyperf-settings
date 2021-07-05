<?php

declare(strict_types=1);

namespace Gokure\Settings;

use Psr\Container\ContainerInterface;

abstract class Store
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * The setting's config
     *
     * @var array
     */
    protected $config;

    /**
     * The setting's items.
     *
     * @var array
     */
    protected $items;

    /**
     * The setting item's original state.
     *
     * @var array
     */
    protected $original;

    /**
     * @var bool
     */
    protected $loaded;

    /**
     * The item's changed state.
     *
     * @var bool
     */
    protected $dirty;

    public function __construct(ContainerInterface $container, array $config)
    {
        $this->container = $container;
        $this->config = $config;
    }

    /**
     * Make sure data is loaded.
     *
     * @param bool $force Force a reload of data. Default false.
     */
    public function load(bool $force = false): void
    {
        if ($this->loaded && !$force) {
            return;
        }

        $this->items = $this->original = $this->read();
        $this->loaded = true;
    }

    /**
     * Get a specific key from settings data.
     *
     * @param array|string $key
     * @param mixed $default
     * @return array|mixed
     */
    public function get($key, $default = null)
    {
        $this->load();
        return Arr::get($this->items, $key, $default);
    }

    /**
     * Set a specific key to a value in the settings data.
     *
     * @param string|array $key
     * @param null $value
     */
    public function set($key, $value = null): void
    {
        $this->load();
        $this->dirty = true;

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                Arr::set($this->items, $k, $v);
            }
        } else {
            Arr::set($this->items, $key, $value);
        }
    }

    /**
     * Determine if a key exists in the settings data.
     *
     * @param string $key
     * @return bool
     */
    public function has($key): bool
    {
        $this->load();
        return Arr::has($this->items, $key);
    }

    /**
     * Unset a key in the settings data.
     *
     * @param string $key
     */
    public function forget(string $key): void
    {
        if ($this->has($key)) {
            $this->dirty = true;
            Arr::forget($this->items, $key);
        }
    }

    /**
     * Clear all keys in the settings data.
     */
    public function flush(): void
    {
        $this->items = [];
        $this->dirty = true;
    }

    /**
     * Get all settings data.
     *
     * @return array
     */
    public function all(): array
    {
        $this->load();
        return $this->items;
    }

    /**
     * Save any changes done to the settings data.
     *
     * @param array $options
     * @return bool
     */
    public function save(array $options = []): bool
    {
        if ($this->dirty) {
            $this->write($this->items);
            $this->dirty = false;
        }

        return true;
    }

    /**
     * Read the data from the store.
     *
     * @return array
     */
    abstract protected function read(): array;

    /**
     * Write the data into the store.
     *
     * @param array $data
     *
     * @return bool
     */
    abstract protected function write(array $data): bool;
}
