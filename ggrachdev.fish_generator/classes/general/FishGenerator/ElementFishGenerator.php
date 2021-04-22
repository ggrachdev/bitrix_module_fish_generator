<?

namespace GGrach\FishGenerator;

use GGrach\FishGenerator\Exceptions\GeneratePhotoException;
use GGrach\FishGenerator\Exceptions\BitrixRedactionException;

/**
 * @todo Add https://dummyimage.com/
 * @todo https://loremflickr.com/ 
 * @todo https://loremipsum.io/ru/21-of-the-best-placeholder-image-generators/
 */

/**
 * Все, что связано с логированием и дебагом
 */
class Debug {

    /**
     * @var bool При Debug режиме записываются все сгенерированные данные в переменную, при отключенном режиме считается только количество ошибок и успешных выполнений
     */
    protected $isDebug = false;

    /**
     * @var bool При строгом режиме выбрасываются исключения при какой-либо ошибке 
     */
    protected $isStrictMode = false;

    /**
     * @var array Результирующие данные
     */
    protected $resultData = [
        // Количество ошибок
        'ERRORS_COUNT' => 0,
        // Описание ошибок
        'ERRORS' => [],
        // Сгенерированные элементы
        'GENERATED_DATA' => [],
        // Успешно сгенерированных элементов
        'SUCCESS_GENERATED' => 0
    ];

    public function addError($dataError) {
        $this->resultData['ERRORS_COUNT']++;

        if ($this->isDebug)
            $this->resultData['ERRORS'][] = $dataError;
    }

    public function addSuccess($dataSuccess) {
        $this->resultData['SUCCESS_GENERATED']++;

        if ($this->isDebug)
            $this->resultData['GENERATED_DATA'][] = $dataSuccess;
    }

    public function clearResultData($dataSuccess) {
        $this->resultData = [
            'ERRORS_COUNT' => 0,
            'ERRORS' => [],
            'GENERATED_DATA' => [],
            'SUCCESS_GENERATED' => 0
        ];
    }

    public function getResultData(): array {
        if ($this->isDebug)
            return $this->resultData;
        else {
            return [
                'ERRORS_COUNT' => $this->resultData['ERRORS_COUNT'],
                'SUCCESS_GENERATED' => $this->resultData['SUCCESS_GENERATED']
            ];
        }
    }

    public function setDebug(bool $valueDebug) {
        $this->isDebug = $valueDebug;
        return $this;
    }

    public function setStrictMode(bool $strictMode) {
        $this->isStrictMode = $strictMode;
        return $this;
    }

}

/**
 * Все, что связано с генерацией фотографий
 */
class PhotoGenerator extends Debug {
    /*
     * Категории фотографи которые можно установить для генерации
     */

    const VALID_CATEGORIES_PHOTO = [
        'abstract', 'animals', 'business',
        'cats', 'city', 'food',
        'fashion', 'people', 'nature',
        'sports', 'technics', 'transport'
    ];

    // @var string|array
    protected $categoryPhoto = null;

    /**
     * Установить категорию фото
     * 
     * @param string|array $categoryPhoto
     * @return \GGrach\Generators\ElementFishGenerator
     */
    public function setCategoryPhoto($categoryPhoto): ElementFishGenerator {

        $isValidPhotoCategory = false;

        if (is_string($categoryPhoto)) {
            if (in_array($categoryPhoto, static::VALID_CATEGORIES_PHOTO)) {
                $this->categoryPhoto = $categoryPhoto;
                $isValidPhotoCategory = true;
            }
        } else if (is_array($categoryPhoto)) {
            $arCorrectCategories = array_intersect(static::VALID_CATEGORIES_PHOTO, $categoryPhoto);

            if (!empty($arCorrectCategories)) {
                $this->categoryPhoto = array_unique($arCorrectCategories);
                $isValidPhotoCategory = true;
                sort($this->categoryPhoto);
            }
        }

        if ($isValidPhotoCategory === false) {
            if ($this->$isStrictMode) {
                if (is_array($categoryPhoto)) {
                    throw new GeneratePhotoException('Not found photos with categories ' . implode(',', $categoryPhoto));
                } else {
                    throw new GeneratePhotoException('Not found photos with category ' . $categoryPhoto);
                }
            }
            if (is_array($categoryPhoto)) {
                $this->addError('Not found photos with categories ' . implode(',', $categoryPhoto));
            } else {
                $this->addError('Not found photos with category ' . $categoryPhoto);
            }
        }

        return $this;
    }

    public function generatePhotoFromLink(string $photoLink): array {
        $pictureArray = \CFile::MakeFileArray($photoLink);

        if (empty($pictureArray['tmp_name'])) {
            throw new GeneratePhotoException('Ошибка сохранения изображения, возможно, у Вас не справляется сервер');
        } else {
            $pictureArray['name'] = $pictureArray['name'] . '.jpg';
        }

        return $pictureArray;
    }

    public function getRandomCategoryPhoto(): string {
        $categoryPhoto = null;

        if ($this->categoryPhoto == null) {
            $categoryPhoto = static::VALID_CATEGORIES_PHOTO[array_rand(static::VALID_CATEGORIES_PHOTO, 1)];
        } else {
            if (is_array($this->categoryPhoto)) {
                $categoryPhoto = $this->categoryPhoto[array_rand($this->categoryPhoto, 1)];
            } else {
                $categoryPhoto = $this->categoryPhoto;
            }
        }

        return $categoryPhoto;
    }

}

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
    public function setPropertyRules(array $arPropertyRules) {

        // @todo
        $needCatalogModule = false;

        if (!empty($arPropertyRules)) {

            $arValidPropertyRules = [];

            foreach ($arPropertyRules as $k => $v) {
                if (is_string($k)) {

                    if ($v === 'randomSection' || $v[1] === 'randomSection') {
                        if (array_key_exists('SECTIONS_' . $this->iblockId, $this->arCache)) {
                            if ($v === 'randomSection') {
                                $v = 'randomElement(' . implode(',', $this->arCache['SECTIONS_' . $this->iblockId]) . ')';
                            } else {
                                $v[1] = 'randomElement(' . implode(',', $this->arCache['SECTIONS_' . $this->iblockId]) . ')';
                            }
                        } else {
                            if (\CModule::IncludeModule("iblock")) {
                                $dataSections = [];

                                $dbSections = \CIBlockSection::GetList(["SORT" => "­­ASC"], ["IBLOCK_ID" => $this->iblockId]);
                                while ($arSection = $dbSections->GetNext()) {
                                    $dataSections[] = $arSection['ID'];
                                }

                                if (empty($dataSections))
                                    $dataSections = [0];

                                $this->arCache['SECTIONS_' . $this->iblockId] = $dataSections;

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
            if (!\CModule::IncludeModule("catalog")) {
                throw new BitrixRedactionException('Not found modul Catalog');
            }
        }

        return $this;
    }

}
