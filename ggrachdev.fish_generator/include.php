<?

include_once 'classes/general/Libs/faker/src/autoload.php';

\Bitrix\Main\Loader::registerAutoLoadClasses('ggrachdev.fish_generator', [
    "\GGrach\CouponsPdf\Generator\CouponGenerator" => "classes/general/CouponsPdf/Generator/CouponGenerator.php"
]);
?>