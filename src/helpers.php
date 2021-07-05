<?php

use Gokure\Settings\SettingManager;
use Hyperf\Utils\ApplicationContext;

if (!function_exists('setting')) {
    /**
     * Get / set the specified setting value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param array|string $key
     * @param mixed $default
     *
     * @return mixed
     */
    function setting($key = null, $default = null)
    {
        if (!ApplicationContext::hasContainer()) {
            throw new \RuntimeException('The application context lacks the container.');
        }

        $store = ApplicationContext::getContainer()->get(SettingManager::class)->getDriver();

        if (is_null($key)) {
            return $store;
        }

        if (is_array($key)) {
            $store->set($key);

            return $store;
        }

        return $store->get($key, $default);
    }
}
