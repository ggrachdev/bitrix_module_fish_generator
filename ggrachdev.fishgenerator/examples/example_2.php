<?php

define("STATISTIC_SKIP_ACTIVITY_CHECK", "true");
define('STOP_STATISTICS', true);
define('PUBLIC_AJAX_MODE', true);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if (\Bitrix\Main\Loader::includeModule('ggrachdev.fishgenerator')) {
    $iblockId = 14;

    $countElements = 1;

    $result = (new \GGrach\FishGenerator\Generators\FishGenerator($iblockId))
            ->setDebugMode(true)
            ->setStrictMode(true)
            ->setGenerationRules([
                '*NAME' => 'realText(50)',
                '*PREVIEW_TEXT' => 'realText(100)',
                '*DETAIL_TEXT' => 'realText(500)',
                'URL' => 'freeEmailDomain',
            ])->generate($countElements);
}

