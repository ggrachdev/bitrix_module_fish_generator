<?

if (!class_exists('\Faker\Factory')) {

    if (\file_exists(__DIR__ . 'classes/general/Libs/Faker/src/autoload.php')) {
        throw new FileNotFoundException('Need upload ( git clone https://github.com/fzaninotto/Faker ) faker library in ' . __DIR__ . '/classes/general/Libs/faker/ from https://github.com/fzaninotto/Faker');
    }

    include_once 'classes/general/Libs/Faker/src/autoload.php';

    if (!class_exists('\Faker\Factory')) {
        throw new FileNotFoundException('Not found \Faker\Factory class. Need upload ( git clone https://github.com/fzaninotto/Faker ) faker library in ' . __DIR__ . '/classes/general/Libs/faker/ from https://github.com/fzaninotto/Faker');
    }
}

\Bitrix\Main\Loader::registerAutoLoadClasses('ggrachdev.fish_generator', [
    // exceptions
    "\GGrach\FishGenerator\Exceptions\BitrixRedactionException" => "classes/general/FishGenerator/Exceptions/BitrixRedactionException.php",
    "\GGrach\FishGenerator\Exceptions\GenerateElementException" => "classes/general/FishGenerator/Exceptions/GenerateElementException.php",
    "\GGrach\FishGenerator\Exceptions\GeneratePhotoException" => "classes/general/FishGenerator/Exceptions/GeneratePhotoException.php",
    "\GGrach\FishGenerator\Exceptions\GeneratorTypeException" => "classes/general/FishGenerator/Exceptions/GeneratorTypeException.php",
    "\GGrach\FishGenerator\Exceptions\SearchIblockException" => "classes/general/FishGenerator/Exceptions/SearchIblockException.php",
    // other
    "\GGrach\FishGenerator\Debug\Debug" => "classes/general/FishGenerator/Debug/Debug.php",
    "\GGrach\FishGenerator\Generators\PhotoGenerator" => "classes/general/FishGenerator/Generators/PhotoGenerator.php",
    "\GGrach\FishGenerator\PropertyRulesElementFilter" => "classes/general/FishGenerator/PropertyRulesElementFilter.php",
    "\GGrach\FishGenerator\Generators\ElementGenerator" => "classes/general/FishGenerator/Generators/ElementGenerator.php",
    "\GGrach\FishGenerator\Cache\RuntimeCache" => "classes/general/FishGenerator/Cache/RuntimeCache.php",
    "\GGrach\FishGenerator\Parser\RuleGenerationParser" => "classes/general/FishGenerator/Parser/RuleGenerationParser.php",
    // entry point
    "\GGrach\FishGenerator\Generators\FishGenerator" => "classes/general/FishGenerator/Generators/FishGenerator.php",
]);
?>