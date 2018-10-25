# Cover
`Krugozor\Cover\CoverArray` - это базовый тип для удобной и гибкой работы с массивами в объектно-ориентированном представлении. Фактически, это "объектный массив". Объекты, производные от `CoverArray` имплементируют PHP SPL-интерфейсы `\IteratorAggregate`, `\Countable`, `\ArrayAccess`, `\Serializable`, для них реализованы магические методы `__set`, `__get`, `__isset`, `__unset` и многое другое.   

## Как это работает? 

Вы можете создать класс, наследуемый от `CoverArray` или использовать `CoverArray` без наследования. Давайте создадим класс нового типа, наследуемый от базового класса `CoverArray`: 

```php
class NewType extends \Krugozor\Cover\CoverArray
{
     
}
```
Инстанцируем новый объект данного класса. Передадим в конструкторе многомерный массив и посмотрим на структуру, которая получится: 

```php
$array = new NewType(['element_1', 'element_2', 'element_3' => array('key1' => 'value1', 'key2' => 'value2')]);
print_r($array);
```
результат отладки: 
```
NewType Object
(
    [data:protected] => Array
        (
            [0] => element_1
            [1] => element_2
            [element_3] => NewType Object
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
1. Переданные в конструктор массивы преобразовались в объекты типа `NewType`. Идеология работы данного решения - все массивы, которые становятся свойствами объектов, наследуемых от `CoverArray`, преобразуются в объекты этого же типа.  
2. Все данные объектов типа `NewType` аккуратно сложились в protected-хранилище `data`, что обеспечивает инкапсуляцию данных.

Давайте попробуем поработать с данными:

получим из объекта элемент данных с индексом 0:
```php
echo $array->item(0);
```
результат: 
```
element_1
```
тоже самое можно было сделать и так:
```php
echo $array[0];
```



Получим значение свойства `key1` вложенного объекта под ключом `element_3`: 
```php
echo $array->element_3->key1;
```
результат: 
```
value1
```



Добавим новый элемент в начало стека данных и сразу получим его значение: 
```php
echo $array->prepend('element_0')->getFirst();
```
результат: 
```
element_0
```



Взглянем объект:
```php
print_r($array);
```
результат: 
```
NewType Object
(
    [data:protected] => Array
        (
            [0] => element_0    // новый элемент добавился в начало массива 
            [1] => element_1
            [2] => element_2
            [element_3] => NewType Object
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



Посчитаем количество элементов у вложенного объекта под ключом `element_3`: 
```php
echo $array->element_3->count();
```
результат: 
```
2
```



Добавим новый элемент во вложенный объект под ключом `element_3`, а после сделаем для него `unset`:
```php
$array->element_3->key3 = 'value3';
print_r($array);
unset($array->element_3->key3);
print_r($array);

// или, можно использоваить такую нотацию:

$array['element_3']['key3'] = 'value3';
print_r($array);
unset($array['element_3']['key3']);
print_r($array);
```
результат: 
```
NewType Object
(
    [data:protected] => Array
        (
            [0] => element_0
            [1] => element_1
            [2] => element_2
            [element_3] => NewType Object
                (
                    [data:protected] => Array
                        (
                            [key1] => value1
                            [key2] => value2
                            [key3] => value3
                        )
                )
        )
)
NewType Object
(
    [data:protected] => Array
        (
            [0] => element_0
            [1] => element_1
            [2] => element_2
            [element_3] => NewType Object
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



Получим все данные объекта как массивы:
```php
print_r($array->getDataAsArray());
```
результат: 
```
Array
(
    [0] => element_0
    [1] => element_1
    [2] => element_2
    [element_3] => Array
        (
            [key1] => value1
            [key2] => value2
        )
)
```



Что будет, если сделать массив свойством объекта `NewType`?
```php
$array->is_array = array(1, 2, 3);
print_r($array->is_array);
```
Присвоенный массив станет элементом объекта типа `NewType`: 
```
NewType Object
(
    [data:protected] => Array
        (
            [0] => 1
            [1] => 2
            [2] => 3
        )
)
```



### разница в написании нотации доступа к свойствам

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
будет создан пустой объект типа `NewType`: 
```
NewType Object
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
NewType Object
(
    [data:protected] => Array
        (
            [non_exists_prop] => NewType Object
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
