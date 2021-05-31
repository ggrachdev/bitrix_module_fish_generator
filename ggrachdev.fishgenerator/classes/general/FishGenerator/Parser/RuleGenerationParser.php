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

    /**
     * Правило не требует подключение генератора
     * 
     * @param string $propertyName
     * @return bool
     */
    private static function isDefaultValue(string $propertyName): bool {
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
        
        return $isDefaultValue;
    }

    private static function getClearPropertyName($propertyName) {
        return \str_replace(['*', '='], '', $propertyName);
    }

    /**
     * 
     * @param string $propertyName
     * @param string | array $typeGenerator
     * @param FishGenerator $generator
     * @return type
     */
    private static function generatePropertyValue(string $propertyName, $ruleGeneration, FishGenerator $generator) {

        $isDefaultValue = self::isDefaultValue($propertyName);

        if (is_array($ruleGeneration)) {

            if (sizeof($ruleGeneration) == 2) {

                if (
                    is_string($ruleGeneration[0]) &&
                    is_numeric($ruleGeneration[1])
                ) {

                    $arParams = self::getParamsFromGeneratorString($ruleGeneration[0]);

                    $count = $ruleGeneration[1];

                    $ruleGeneration = array_shift($arParams);

                    if($isDefaultValue)
                    {
                        $valuePropety = [];
                        
                        for($i = 0; $i <= $count; $i++)
                        {
                            $valuePropety[] = str_replace('$', ($i+1), $ruleGeneration);
                        }
                    }
                    else
                    {
                        $valuePropety = $generator->generateItem($ruleGeneration, $arParams, $count);
                    }
                }
            }
        } else {
            
            $arParams = self::getParamsFromGeneratorString($ruleGeneration);
            $ruleGeneration = array_shift($arParams);

            $valuePropety = null;

            if ($isDefaultValue) {
                $valuePropety = $ruleGeneration;
            } else {
                $valuePropety = $generator->generateItem($ruleGeneration, $arParams);
            }
        }

        return $valuePropety;
    }

    private static function getParamsFromGeneratorString(string $generatorString) {
        return explode('(', str_replace(')', '', $generatorString));
    }

    public static function parse(array $arInputRules, FishGenerator $generator) {

        $arValidPropertiesForGeneration = [
            'PROPERTIES' => [],
            'STANDART_PROPERTIES' => []
        ];

        if (!empty($arInputRules)) {

            foreach ($arInputRules as $propertyName => $ruleGeneration) {

                $valuePropety = self::generatePropertyValue($propertyName, $ruleGeneration, $generator);

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
