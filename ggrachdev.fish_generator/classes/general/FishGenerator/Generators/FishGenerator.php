<?

namespace GGrach\FishGenerator\Generators;

use GGrach\FishGenerator\Exceptions\SearchIblockException;

/**
 * @todo Добавить исключения
 */
final class FishGenerator extends ElementGenerator {

    /**
     * @var array Кеш
     */
    public $arCache = [];

    /*
     * @var int ID Инфоблока в который будет осуществлена генерация
     */
    protected $iblockId = null;

    /**
     * 
     * @param int $iblockId
     * @param string $localization Локализация
     * @throws BitrixRedactionException
     */
    public function __construct(int $iblockId, string $localization = 'ru_RU') {

        if (\Bitrix\Main\Loader::includeModule("iblock")) {
            $dbRes = \CIBlock::GetList(
                    [],
                    ["ID" => $iblockId]
            );

            if (!$dbRes->GetNext()) {
                throw new SearchIblockException('Указаный инфоблок не найден');
            }
            $this->dataGenerator = \Faker\Factory::create($localization);
            $this->iblockId = $iblockId;
        } else {
            throw new BitrixRedactionException('Не найдены необходимые для работы библиотеки модули');
        }
    }

}
