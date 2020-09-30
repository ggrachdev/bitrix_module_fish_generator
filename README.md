Библиотека находится в разработке

Библиотека для генерации тестовых элементов для 1с Битрикс на основе Faker

Пример использования:

```php
<?php
use Fred\FishGenerator\ElementFishGenerator;

/** 
* В конструктор передаем IBLOCK ID в который нужно сгенерировать тестовый элемент
* При setDebug = true в результирующий массив записываются данные для генерации
* При setStrictMode = true выбрасываются Exception'ы если что-то идет не так
* 
*/

$result = (new ElementFishGenerator(6))->setDebug(true)->setStrictMode(true)->setCategoryPhoto(['technics', 'business', 'city'])->setPropertyRules([
       '*=NAME' => 'Тестовый элемент $',
       'PRODUCTION_PHOTOS' => [
           'image', 7
       ],
       'IMPLEMENTED_PROCESSES_POINTS' => [
           'randomElement(Тестовый пункт, Еще один пункт, Пункт производства, Новый пункт, Пункт элемента, Тестовый процесс, Процесс производства, Новый процесс производства)', 5
       ],
       'IMPLEMENTED_PROCESSES_VALUES' => [
           'realText(100)', 5
       ],
       '*IBLOCK_SECTION_ID' => 'randomSection'
   ])->generate(1);
   
print_r($result);
?>
```

Доступные способы для генерации:
inn
name
kpp
address
realText(100)
word
city
country
phoneNumber
company
email
streetAddress
date
time
year
jobTitle
numberBetween(0)(1000)
randomElement(1,2,3,4)
lastName
firstName
latitude
longitude
hexcolor
image
image(1000, 500) // Ширина, высота
