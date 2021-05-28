<?

namespace GGrach\FishGenerator\Generators;

use GGrach\FishGenerator\Exceptions\SearchIblockException;

final class FishGenerator extends ElementGenerator {
    
    /*
     * @var int ID Инфоблока в который будет осуществлена генерация
     */
    protected $iblockId = null;

    /**
     * @param int $iblockId
     * @param string $localization Локализация
     * @throws BitrixRedactionException
     */
    public function __construct(int $iblockId, string $localization = 'ru_RU') {

        if ($iblockId <= 0) {
            throw new SearchIblockException('Iblock id ' . $iblockId . ' can be above zero ');
        }

        if (\Bitrix\Main\Loader::includeModule("iblock")) {
            $dbRes = \CIBlock::GetList(
                    [],
                    ["ID" => $iblockId]
            );

            if (!$dbRes->GetNext()) {
                throw new SearchIblockException('Not found iblock with id ' . $iblockId);
            }
            $this->dataGenerator = \Faker\Factory::create($localization);
            $this->iblockId = $iblockId;
        } else {
            throw new BitrixRedactionException('Modules required for the library to work were not found');
        }
    }

}
