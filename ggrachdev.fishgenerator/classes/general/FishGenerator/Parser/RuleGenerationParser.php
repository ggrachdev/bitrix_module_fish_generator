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

    public static function parse(array $arInputRules, FishGenerator $generator) {

        $arValidPropertiesForGeneration = [
            'PROPERTIES' => [],
            'STANDART_PROPERTIES' => []
        ];

        if (!empty($arInputRules)) {

            foreach ($arInputRules as $propertyName => $typeGenerator) {

                $isSystemBitrixProperty = in_array(str_replace(['*', '='], ['', ''], $propertyName), static::SYSTEM_PROPERTIES) && ($propertyName[0] == '*' || $propertyName[1] == '*');

                $isDefaultValue = false;

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
                    $arParams = explode('(', str_replace(')', '', $typeGenerator));
                    $typeGenerator = array_shift($arParams);

                    $valuePropety = null;

                    if (!$isDefaultValue) {
                        $valuePropety = $generator->generateItem($typeGenerator, $arParams);
                    } else {
                        $valuePropety = $typeGenerator;
                    }
                } else {

                    if (sizeof($typeGenerator) == 2) {

                        if (is_numeric($typeGenerator[1]) && is_string($typeGenerator[0])) {

                            $count = $typeGenerator[1];

                            $arParams = explode('(', str_replace(')', '', $typeGenerator[0]));
                            $typeGenerator = array_shift($arParams);

                            $valuePropety = $generator->generateItem($typeGenerator, $arParams, $count);
                        }
                    }
                }

                if ($valuePropety !== null) {
                    if ($isSystemBitrixProperty) {
                        $arValidPropertiesForGeneration['STANDART_PROPERTIES'][str_replace(['*', '='], ['', ''], $propertyName)] = $valuePropety;
                    } else {
                        $arValidPropertiesForGeneration['PROPERTIES'][str_replace(['*', '='], ['', ''], $propertyName)] = $valuePropety;
                    }
                }
            }
        }

        return $arValidPropertiesForGeneration;
    }

}
