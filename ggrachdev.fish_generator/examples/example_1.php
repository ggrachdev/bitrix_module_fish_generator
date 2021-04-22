<?php

if (\Bitrix\Main\Loader::includeModule('ggrachdev.fish_generator')) {
    // Инфоблок куда нужно сгенерировать элементы
    $iblockId = 1;

    // Нужно сгенерировать 10 элементов
    $countElements = 10;

    $result = (new \GGrach\FishGenerator\Generators\FishGenerator($iblockId))
            ->setDebug(true)
            ->setStrictMode(true)
            ->setGenerationRules([
                '*=NAME' => 'Тестовый элемент $',
            ])->generate($countElements);

    /**
     * В результате получим 10 новых элементов с именами:
     * 
     * Тестовый элемент 1
     * Тестовый элемент 2
     * Тестовый элемент 3
     * Тестовый элемент 4
     * ...
     * Тестовый элемент 10
     */
    
    /**
     * Так же у элементов будет заполнена картинка анонса и детальная картинка, детальный текст, текст анонса, символьный код
     */
}