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
    public function __set(string $key, $value)
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
     * @return \ArrayIterator
     */
    final public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * Возвращает элемент коллекции с заданным индексом в качестве результата.
     * Аналог parent::__get, но предназначен для числовых индексов.
     *
     * @param int $key
     * @return null|mixed
     */
    final public function item(int $key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * Присоединяет один элемент в начало массива.
     *
     * @param $value
     * @return AbstractArray
     */
    final public function prepend($value): self
    {
        array_unshift($this->data, $this->array2cover($value));

        return $this;
    }

    /**
     * Присоединяет один элемент в конец массива.
     *
     * @param $value
     * @return AbstractArray
     */
    final public function append($value): self
    {
        array_push($this->data, $this->array2cover($value));

        return $this;
    }

    /**
     * Возвращает последний элемент массива.
     *
     * @return mixed
     */
    final public function getLast()
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
    final public function getFirst()
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
     * Реализация метода интерфейса ArrayAccess.
     *
     * @param mixed $key ключ элемента
     * @param mixed $value значение элемента
     */
    final public function offsetSet($key, $value)
    {
        // Это присвоение нового элемента массиву типа $var[] = 'element';
        if ($key === null) {
            $u = &$this->data[];
        } else {
            $u = &$this->data[$key];
        }

        $u = $this->array2cover($value);
    }

    /**
     * Реализация метода интерфейса ArrayAccess.
     *
     * @param mixed ключ элемента
     * @return bool
     */
    final public function offsetExists($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Реализация метода интерфейса ArrayAccess.
     *
     * @param mixed ключ элемента
     */
    final public function offsetUnset($key)
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
        }
    }

    /**
     * Реализация метода интерфейса ArrayAccess.
     *
     * В случае отсутствия запрошеного элемента не генерирует Notice: Undefined index,
     * а создает в вызвавшем его объекте, в хранилище, свойство $key содержащее пустой объект текущего типа.
     *
     * Это поведение изначально было предназначено для view, когда в шаблоне можно писать
     * переменные, которые могут быть не объявлены. Например:
     * echo $data['non_exists']['var']; // пустая строка - сработал __toString()
     *
     * Или можно объявлять цепочку вложенных элементов:
     * $array['non_exists_prop']['non_exists_prop']['property'] = true;
     * - здесь будут созданы вложенные объекты, т.е. данное условие идентично вызову
     * $array->non_exists_prop->non_exists_prop->property == true
     *
     * @param mixed ключ элемента
     * @return $this
     */
    final public function offsetGet($key)
    {
        if (!isset($this->data[$key])) {
            $this->data[$key] = new $this();
        }

        return $this->data[$key];
    }

    /**
     * Реализация метода интерфейса Serializable.
     *
     * @return string
     */
    final public function serialize()
    {
        return serialize($this->data);
    }

    /**
     * Реализация метода интерфейса Serializable.
     *
     * @param array $data
     * @return $this
     */
    final public function unserialize($data)
    {
        $this->setData(unserialize($data));

        return $this;
    }

    /**
     * @see array_reverse
     * @return array
     */
    final public function reverse()
    {
        return array_reverse($this->data);
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
            $in[$key] = (is_object($value) && $value instanceof self)
                ? $in[$key] = self::object2array($value->getData())
                : $value;
        }

        return $in;
    }

    /**
     * Возвращает объект текущего типа, если переданным
     * в метод значением является массив.
     *
     * @param $value AbstractArray|array
     * @return AbstractArray
     */
    final protected function array2cover($value)
    {
        return is_array($value) ? new $this($value) : $value;
    }
}