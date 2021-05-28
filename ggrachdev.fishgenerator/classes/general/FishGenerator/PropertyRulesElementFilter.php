<?

namespace GGrach\FishGenerator;

use GGrach\FishGenerator\Exceptions\BitrixRedactionException;
use GGrach\FishGenerator\Generators\PhotoGenerator;
use GGrach\FishGenerator\Cache\RuntimeCache;

/**
 * @todo Add https://dummyimage.com/
 * @todo https://loremflickr.com/ 
 * @todo https://loremipsum.io/ru/21-of-the-best-placeholder-image-generators/
 */

/**
 * Логика установки и первоначальная валидация правил для генерации.
 * Цель: отфильтровать возможные ошибки указанных пользователем правил генерации и выдать максимально корректный массив, чтобы не было
 * ошибок на последующих стадиях
 */
class PropertyRulesElementFilter extends PhotoGenerator {
    /*
     * @var array Провалидированные правила для генерации
     */

    protected $arPropertyGenerateRules = [];

    /**
     * Установить правила генерации свойств
     * 
     * @param array $arPropertyRules
     * @return $this
     */
    public function setGenerationRules(array $arPropertyRules) {

        // @todo
        $needCatalogModule = false;

        if (!empty($arPropertyRules)) {

            $arValidPropertyRules = [];

            foreach ($arPropertyRules as $k => $v) {
                if (is_string($k)) {

                    if ($v === 'randomSection' || $v[1] === 'randomSection') {

                        $keyCacheSection = 'SECTIONS_' . $this->iblockId;

                        if (RuntimeCache::has($keyCacheSection)) {
                            if ($v === 'randomSection') {
                                $v = 'randomElement(' . implode(',', RuntimeCache::get($keyCacheSection)) . ')';
                            } else {
                                $v[1] = 'randomElement(' . implode(',', RuntimeCache::get($keyCacheSection)) . ')';
                            }
                        } else {
                            if (\CModule::IncludeModule("iblock")) {
                                $dataSections = [];

                                $dbSections = \CIBlockSection::GetList(["SORT" => "­­ASC"], ["IBLOCK_ID" => $this->iblockId]);
                                while ($arSection = $dbSections->GetNext()) {
                                    $dataSections[] = $arSection['ID'];
                                }

                                if (empty($dataSections)) {
                                    $dataSections = [0];
                                }

                                RuntimeCache::set($keyCacheSection, $dataSections);

                                if ($v === 'randomSection') {
                                    $v = 'randomElement(' . implode(',', $dataSections) . ')';
                                } else {
                                    $v[1] = 'randomElement(' . implode(',', $dataSections) . ')';
                                }
                            } else {
                                throw new BitrixRedactionException('Not found modul iblock');
                            }
                        }
                    }

                    $arValidPropertyRules[trim($k)] = $v;
                }
            }

            $this->arPropertyGenerateRules = $arValidPropertyRules;
        }

        if ($needCatalogModule) {
            if (!\Bitrix\Main\Loader::includeModule("catalog")) {
                throw new BitrixRedactionException('Not found modul Catalog');
            }
        }

        return $this;
    }

}
