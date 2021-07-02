<?php

declare(strict_types=1);

namespace Gokure\Settings\Store;

use Hyperf\Utils\Filesystem\Filesystem;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use RuntimeException;

class FileSystemStore extends Store
{
    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var string
     */
    protected $path;

    public function __construct(ContainerInterface $container, array $config)
    {
        parent::__construct($container, $config);

        $this->files = $container->get(Filesystem::class);
        $this->setPath($config['path'] ?? BASE_PATH . '/runtime/settings.json');
    }

    /**
     * Set the path for the JSON file.
     *
     * @param string $path
     * @return $this
     */
    public function setPath($path): self
    {
        // If the file does not already exist, we will attempt to create it.
        if (!$this->files->exists($path)) {
            $result = $this->files->put($path, '{}');
            if ($result === false) {
                throw new InvalidArgumentException("Could not write to $path.");
            }
        }

        if (!$this->files->isWritable($path)) {
            throw new InvalidArgumentException("$path is not writable.");
        }

        $this->path = $path;

        return $this;
    }

    /**
     * Get the path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    protected function read(): array
    {
        $contents = $this->files->get($this->path);

        $data = json_decode($contents, true);

        if ($data === null) {
            throw new RuntimeException("Invalid JSON in {$this->path}");
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $data): bool
    {
        if ($data) {
            $contents = json_encode($data);
        } else {
            $contents = '{}';
        }

        return (bool)$this->files->put($this->path, $contents);
    }
}
