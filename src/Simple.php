<?php

declare(strict_types=1);

namespace Krugozor\Cover;

/**
 * @package Krugozor\Cover
 * @author Vasiliy Makogon
 * @link https://github.com/Vasiliy-Makogon/Cover
 */
trait Simple
{
    /** @var array */
    protected array $data = [];

    /**
     * @param string $key
     * @return mixed
     */
    public function __get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function __set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * @param string $key
     */
    public function __unset(string $key): void
    {
        unset($this->data[$key]);
    }

    /**
     * Returns the collection element with the given index as the result.
     * Similar to the __get method, but intended for numeric indices.
     *
     * @param mixed $key
     * @return mixed
     */
    public function item(mixed $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    /**
     * Returns the current object's data, i.e. the contents of the $data property.
     * Most likely, you don't need this method in your work,
     * pay attention to the CoverArray::getDataAsArray method!
     *
     * @return array
     * @see CoverArray::getDataAsArray()
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param iterable|null $data
     * @return static
     */
    public function setData(?iterable $data): static
    {
        if ($data) {
            foreach ($data as $key => $value) {
                $this->data[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * @return static
     */
    public function clear(): static
    {
        $this->data = [];

        return $this;
    }
}