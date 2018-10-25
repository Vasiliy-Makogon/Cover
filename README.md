# Cover
`\Krugozor\Cover` - библиотека для удобной и гибкой работы с массивами в объектно-ориентированном представлении. 

## Как это работает? 

Создадим объект нового типа, наследуемый от базового объекта `\Krugozor\Cover\CoverArray`

```php
class NewMyType extends \Krugozor\Cover\CoverArray
{

}
```
Инстанцируем новый объект данного типа. Передадим в конструкторе многомерный массив и посмотрим на структуру, которая получится: 

```php
$array = new NewMyType(['foo', 12345, 'element' => array('key1' => 'value1', 'key2' => 'value2')]);
print_r($array);
```
результат отладки: 
```
NewMyType Object
(
    [data:protected] => Array
        (
            [0] => foo
            [1] => 12345
            [element] => NewMyType Object
                (
                    [data:protected] => Array
                        (
                            [key1] => value1
                            [key2] => value2
                        )

                )

        )

)
```
Как видно, произошло два крайне важных события: 
1. Переданные в конструктор массивы преобразовались в тип `NewMyType` - это идеология данной библиотеки.
2. Все данные объекта `NewMyType` аккуратно сложились в protected-хранилище `data`, что обеспечивает инкапсуляцию данных.

Давайте попробуем поработать с данными:

получим из объекта элемент данных с индексом 0:
```php
echo $array->item(0);
```
результат: 
```
foo
```
тоже самое можно сделать так:
```php
echo $array[0];
```

Получим значение свойства `key1` вложенного объекта под ключом `element`: 
```php
echo $array->element->key1;
```
результат: 
```
value1
```

Добавим новый элемент в начало стека данных и сразу получим его значение: 
```php
echo $array->prepend('the first element')->getFirst();
```
результат: 
```
the first element
```

Посчитаем количество элементов у вложенного объекта под ключом `element`: 
```php
echo $array->element->count();
```
результат: 
```
2
```

Добавим новый элемент во вложенный объект под ключом `element` и запросим его значение:
```php
echo $array->element->append('Hellow, PHP!')->item(0);
```
результат: 
```
Hellow, PHP!
```

Получим все данные объекта как массивы:
```php
print_r($array->getDataAsArray());
```
результат: 
```
Array
(
    [0] => the first element
    [1] => foo
    [2] => 12345
    [element] => Array
        (
            [key1] => value1
            [key2] => value2
            [0] => Hellow, PHP!
        )

)

```

Что будет, если присвоить массив в объект `NewMyType`?
```php
$array->is_array = array(1, 2, 3);
print_r($array->is_array);
```
Присвоенный массив станет элементом объекта типа `NewMyType`: 
```
NewMyType Object
(
    [data:protected] => Array
        (
            [0] => 1
            [1] => 2
            [2] => 3
        )

)
```

Если запросить не существующее свойство у объекта как свойство, то вернётся NULL:
```php
var_dump($array->non_exists_prop);
```
результат: 
```
NULL
```

Однако, если запросить не существующее свойство у объекта в массивной нотации доступа, то...  
```php
print_r($array['non_exists_prop']);
```
будет создан пустой объект типа `NewMyType`: 
```
NewMyType Object
(
    [data:protected] => Array
        (
        )

)
```

...это позволяет делать цепочки вложенных объектов:
```php
$array['non_exists_prop']['non_exists_prop']['property'] = true;
print_r($array['non_exists_prop']);
```
результат: 
```
NewMyType Object
(
    [data:protected] => Array
        (
            [non_exists_prop] => NewMyType Object
                (
                    [data:protected] => Array
                        (
                            [property] => 1
                        )

                )

        )

)
```

...данная функциональность была создана для PHP-шаблонизации: несуществующие по каким-либо причинам данные не вызывают ошибки при `echo`:
```php
echo $array['non_exists_prop']['non_exists_prop'];
```
результат: 
```
// будет выведена пустая строка 
```
