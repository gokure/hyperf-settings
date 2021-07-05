<?php

declare(strict_types=1);

namespace Gokure\Settings;

use Closure;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Utils\Arr as HyperfArr;
use Psr\Container\ContainerInterface;

class DatabaseStore extends Store
{
    /**
     * @var \Hyperf\Database\ConnectionInterface
     */
    protected $connection;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $keyColumn;

    /**
     * @var string
     */
    protected $valueColumn;

    /**
     * Any query constraints that should be applied.
     *
     * @var Closure|null
     */
    protected $queryConstraint;

    /**
     * Any extra columns that should be added to the rows.
     *
     * @var array
     */
    protected $extraColumns = [];

    public function __construct(ContainerInterface $container, array $config)
    {
        parent::__construct($container, $config);

        /** @var \Hyperf\DbConnection\ConnectionResolver $resolver */
        $resolver = $this->container->get(ConnectionResolverInterface::class);
        $this->connection = $resolver->connection($config['database']['connection']);
        $this->table = $config['database']['table'] ?: 'settings';
        $this->keyColumn = $config['database']['key_column'] ?: 'key';
        $this->valueColumn = $config['database']['value_column'] ?: 'value';
    }

    /**
     * Set the table to query from.
     *
     * @param string $table
     */
    public function setTable($table): self
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Set the key column name to query from.
     *
     * @param string $keyColumn
     * @return $this
     */
    public function setKeyColumn($keyColumn): self
    {
        $this->keyColumn = $keyColumn;

        return $this;
    }

    /**
     * Set the value column name to query from.
     *
     * @param string $valueColumn
     * @return $this
     */
    public function setValueColumn($valueColumn): self
    {
        $this->valueColumn = $valueColumn;

        return $this;
    }

    /**
     * Set the query constraint.
     *
     * @param Closure $callback
     * @return $this
     */
    public function setConstraint(Closure $callback): self
    {
        $this->items = [];
        $this->loaded = false;
        $this->queryConstraint = $callback;

        return $this;
    }

    /**
     * Set extra columns to be added to the rows.
     *
     * @param array $columns
     * @return $this
     */
    public function setExtraColumns(array $columns): self
    {
        $this->extraColumns = $columns;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function read(): array
    {
        return $this->parseReadData($this->newQuery()->get());
    }

    /**
     * Parse data coming from the database.
     *
     * @param array|\Hyperf\Utils\Collection $data
     *
     * @return array
     */
    public function parseReadData($data): array
    {
        $results = [];

        foreach ($data as $row) {
            if (is_array($row)) {
                $key = $row[$this->keyColumn];
                $value = $row[$this->valueColumn];
            } elseif (is_object($row)) {
                $key = $row->{$this->keyColumn};
                $value = $row->{$this->valueColumn};
            } else {
                $message = 'Expected array or object, got ' . gettype($row);
                throw new \UnexpectedValueException($message);
            }

            Arr::set($results, $key, $value);
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function forget($key): void
    {
        parent::forget($key);

        // because the database driver cannot store empty arrays, remove empty
        // arrays to keep data consistent before and after saving
        $segments = explode('.', $key);
        array_pop($segments);

        while ($segments) {
            $segment = implode('.', $segments);

            // non-empty array - exit out of the loop
            if ($this->get($segment)) {
                break;
            }

            // remove the empty array and move on to the next segment
            $this->forget($segment);
            array_pop($segments);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $data): bool
    {
        $changes = $deletes = [];
        $inserts = HyperfArr::dot($data);
        $items = HyperfArr::dot($this->items);
        $original = HyperfArr::dot($this->original);

        $keys = $this->newQuery()->pluck($this->keyColumn);
        foreach ($keys as $key) {
            if (!isset($inserts[$key])) {
                $deletes[] = $key;
            } elseif (isset($items[$key], $original[$key]) && (string)$items[$key] !== (string)$original[$key]) {
                $changes[$key] = $items[$key];
            }
            unset($inserts[$key]);
        }

        foreach ($changes as $key => $value) {
            $this->newQuery()
                ->where($this->keyColumn, '=', (string)$key)
                ->update([$this->valueColumn => $value]);
        }

        if ($inserts) {
            $this->newQuery(true)
                ->insert($this->prepareInsertData($inserts));
        }

        if ($deletes) {
            $this->newQuery()
                ->whereIn($this->keyColumn, $deletes)
                ->delete();
        }

        return true;
    }

    /**
     * Transforms settings data into an array ready to be insterted into the
     * database. Call HyperfArr::dot on a multi-dimensional array before passing it
     * into this method!
     *
     * @param array $data Call HyperfArr::dot on a multi-dimensional array before passing it into this method!
     *
     * @return array
     */
    protected function prepareInsertData(array $data): array
    {
        $results = [];
        if ($this->extraColumns) {
            foreach ($data as $key => $value) {
                $results[] = array_merge(
                    $this->extraColumns,
                    [$this->keyColumn => $key, $this->valueColumn => $value]
                );
            }
        } else {
            foreach ($data as $key => $value) {
                $results[] = [$this->keyColumn => $key, $this->valueColumn => $value];
            }
        }

        return $results;
    }

    /**
     * Create a new query builder instance.
     *
     * @param bool $insert Whether the query is an insert or not.
     * @return \Hyperf\Database\Query\Builder
     */
    protected function newQuery(bool $insert = false): \Hyperf\Database\Query\Builder
    {
        $query = $this->connection->table($this->table);

        if (!$insert) {
            foreach ($this->extraColumns as $key => $value) {
                $query->where($key, '=', $value);
            }
        }

        if ($this->queryConstraint !== null) {
            $callback = $this->queryConstraint;
            $callback($query, $insert);
        }

        return $query;
    }
}
