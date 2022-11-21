<?php

namespace Krugozor\Cover;

/**
 * Обёртка массива, хранилище.
 * Попытка реализации объекта для более удобной работы с массиво-образной структурой данных.
 */
class CoverArray implements \IteratorAggregate, \Countable, \ArrayAccess, \Serializable
{
    use Simple;

    /**
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        foreach ($data as $key => $value) {
            $this->data[$key] = $this->array2cover($value);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '';
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function __set(string $key, mixed $value): void
    {
        $this->data[$key] = $this->array2cover($value);
    }

    /**
     * Реализация интерфейса Countable
     *
     * @return int
     */
    final public function count(): int
    {
        return count($this->data);
    }

    /**
     * Реализация интерфейса IteratorAggregate
     *
     * @return \ArrayIterator
     */
    final public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * Присоединяет один элемент в начало массива.
     *
     * @param mixed $value
     * @return $this
     */
    final public function prepend(mixed $value): self
    {
        array_unshift($this->data, $this->array2cover($value));

        return $this;
    }

    /**
     * Присоединяет один элемент в конец массива.
     *
     * @param mixed $value
     * @return $this
     */
    final public function append(mixed $value): self
    {
        array_push($this->data, $this->array2cover($value));

        return $this;
    }

    /**
     * Возвращает последний элемент массива.
     *
     * @return mixed
     */
    final public function getLast(): mixed
    {
        $last = end($this->data);
        reset($this->data);

        return $last;
    }

    /**
     * Возвращает первый элемент массива.
     *
     * @return mixed
     */
    final public function getFirst(): mixed
    {
        reset($this->data);
        $first = current($this->data);
        reset($this->data);

        return $first;
    }

    /**
     * Возвращает данные объекта как массив.
     *
     * @return array
     */
    final public function getDataAsArray(): array
    {
        return self::object2array($this->data);
    }

    /**
     * Реализация метода интерфейса ArrayAccess::offsetSet.
     *
     * @param mixed $offset
     * @param mixed $value
     */
    final public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->data[] = $this->array2cover($value);
        } else {
            $this->data[$offset] = $this->array2cover($value);
        }
    }

    /**
     * Реализация метода интерфейса ArrayAccess::offsetExists.
     *
     * @param mixed $key
     * @return bool
     */
    final public function offsetExists(mixed $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Реализация метода интерфейса ArrayAccess::offsetUnset.
     *
     * @param mixed $key
     */
    final public function offsetUnset(mixed $key): void
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
        }
    }

    /**
     * Реализация метода интерфейса ArrayAccess.
     *
     * В случае отсутствия запрошенного элемента не генерирует Notice: Undefined index,
     * а создает в вызвавшем его объекте, в хранилище, свойство $key содержащее пустой объект текущего типа.
     *
     * Таким образом, можно объявлять цепочку вложенных элементов:
     * $array['non_exists_prop']['non_exists_prop_2']['property'] = true;
     *
     * В этом примере будут созданы вложенные объекты $array->non_exists_prop->non_exists_prop_2
     * а объект non_exists_prop_2 будет содержать свойство property == true:
     *
     * $array->non_exists_prop->non_exists_prop->property == true
     *
     * @param mixed $key
     * @return $this
     */
    final public function offsetGet(mixed $key): mixed
    {
        if (!isset($this->data[$key])) {
            $this->data[$key] = new $this();
        }

        return $this->data[$key];
    }

    /**
     * Реализация интерфейса Serializable.
     *
     * @return string|null
     */
    final public function serialize(): ?string
    {
        return serialize($this->data);
    }

    /**
     * Реализация интерфейса Serializable.
     *
     * @param string $data
     */
    final public function unserialize($data): void
    {
        $this->setData(unserialize($data));
    }

    /**
     * @see array_reverse
     * @return array
     */
    final public function reverse(): array
    {
        return array_reverse($this->data);
    }

    /**
     * Возвращает данные по ключам многомерных массивов через dot-нотацию.
     * Пример массива: a[b][c]=true
     * Пример доступа:
     *    $...->get('a.b.c')
     *
     * @param string $path
     * @param mixed $data
     * @return mixed
     */
    final public function get(string $path, mixed $data = null): mixed
    {
        if (!$path) {
            return null;
        }

        list(0 => $key, 1 => $other) = array_pad(explode('.', $path, 2), 2, null);

        $actual_data = $data === null
            ? ($this->data[$key] ?? null)
            : $data[$key];

        // Закончились ключи в цепочке следования
        if (!$other) {
            return $actual_data;
        }

        // Попытка вызывать ключ на скалярном значении
        $selfClass = __CLASS__;
        if (!is_object($actual_data) || !$actual_data instanceof $selfClass) {
            return new $this;
        }

        return $this->get($other, $actual_data);
    }

    /**
     * Преобразует все значения массива $in в массивы, если значения
     * каких-либо элементов данных будут объекты текущего типа.
     *
     * @param array
     * @return array
     */
    final protected static function object2array(array $in): array
    {
        foreach ($in as $key => $value) {
            $in[$key] = is_object($value) && $value instanceof self
                ? $in[$key] = self::object2array($value->getData())
                : $value;
        }

        return $in;
    }

    /**
     * Возвращает объект текущего типа ($this), если переданным в метод значением является массив.
     * Вложенные элементы массива так же становятся объектом типа $this
     *
     * @param mixed $value
     * @return mixed
     */
    final protected function array2cover(mixed $value): mixed
    {
        return is_array($value) ? new $this($value) : $value;
    }
}