<?php

namespace GGrach\FishGenerator\Parser;

use GGrach\FishGenerator\Generators\FishGenerator;

class RuleGenerationParser {

    /**
     * Свойства которые считаются за системные свойства битрикса
     */
    const SYSTEM_PROPERTIES = [
        'NAME', 'ACTIVE', 'CODE', 'IBLOCK_SECTION_ID', 'DETAIL_TEXT', 'PREVIEW_TEXT', 'SORT'
    ];

    private static function isSystemProperty(string $propertyName): bool {
        $clearPropertyCode = self::getClearPropertyName($propertyName);
        return in_array($clearPropertyCode, static::SYSTEM_PROPERTIES) && ($propertyName[0] == '*' || $propertyName[1] == '*');
    }
    
    private static function getClearPropertyName($propertyName)
    {
        return str_replace(['*', '='], '', $propertyName);
    }

    private static function generatePropertyValue(string $propertyName, string $typeGenerator, FishGenerator $generator) {
        $isDefaultValue = false;

        $isSystemBitrixProperty = self::isSystemProperty($propertyName);

        if ($isSystemBitrixProperty) {
            if ($propertyName[0] === '=' || $propertyName[1] === '=') {
                $isDefaultValue = true;
            }
        } else {
            if ($propertyName[0] === '=') {
                $isDefaultValue = true;
            }
        }

        if (!is_array($typeGenerator)) {
            $arParams = self::getParamsFromGeneratorString($typeGenerator);
            $typeGenerator = array_shift($arParams);

            $valuePropety = null;

            if (!$isDefaultValue) {
                $valuePropety = $generator->generateItem($typeGenerator, $arParams);
            } else {
                $valuePropety = $typeGenerator;
            }
        } else {

            if (sizeof($typeGenerator) == 2) {

                if (
                    is_string($typeGenerator[0]) && 
                    is_numeric($typeGenerator[1])
                ) {

                    $count = $typeGenerator[1];

                    $arParams = self::getParamsFromGeneratorString($typeGenerator[0]);
                    
                    $typeGenerator = array_shift($arParams);

                    $valuePropety = $generator->generateItem($typeGenerator, $arParams, $count);
                }
            }
        }

        return $valuePropety;
    }
    
    private static function getParamsFromGeneratorString(string $generatorString)
    {
        return explode('(', str_replace(')', '', $generatorString));
    }

    public static function parse(array $arInputRules, FishGenerator $generator) {

        $arValidPropertiesForGeneration = [
            'PROPERTIES' => [],
            'STANDART_PROPERTIES' => []
        ];

        if (!empty($arInputRules)) {

            foreach ($arInputRules as $propertyName => $typeGenerator) {

                $valuePropety = self::generatePropertyValue($propertyName, $typeGenerator, $generator);

                if ($valuePropety !== null) {
                
                    $clearPropertyCode = self::getClearPropertyName($propertyName);
                
                    if (self::isSystemProperty($propertyName)) {
                        $arValidPropertiesForGeneration['STANDART_PROPERTIES'][$clearPropertyCode] = $valuePropety;
                    } else {
                        $arValidPropertiesForGeneration['PROPERTIES'][$clearPropertyCode] = $valuePropety;
                    }
                }
            }
        }

        return $arValidPropertiesForGeneration;
    }

}
