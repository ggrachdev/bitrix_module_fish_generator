<?

namespace GGrach\FishGenerator\Generators;

use GGrach\FishGenerator\Exceptions\GenerateElementException;
use GGrach\FishGenerator\Exceptions\SearchIblockException;
use GGrach\FishGenerator\Exceptions\GeneratorTypeException;
use GGrach\FishGenerator\PropertyRulesElementFilter;

/**
 * Логика генерации элемента
 */
class ElementGenerator extends PropertyRulesElementFilter {

    /**
     * Свойства которые считаются за стандартные свойства битрикса
     */
    const STANDART_PROPERTIES = [
        'NAME', 'ACTIVE', 'CODE', 'IBLOCK_SECTION_ID', 'DETAIL_TEXT', 'PREVIEW_TEXT', 'SORT'
    ];

    /**
     * @var Object Адаптированный генератор
     * @todo Добавить интерфейс, возможность сменить генератор
     */
    public $dataGenerator = null;

    /**
     * Генерация элементов
     * 
     * @param int $countElements
     * @throws GenerateElementException
     */
    public function generate(int $countElements) {
        if ($countElements < 0) {
            $this->addError('Need more less 0 generated Elements ' . __LINE__);

            if ($this->isStrictMode) {
                throw new GenerateElementException('Need more less 0 generated Elements');
            }
        }

        if (!empty($this->arPropertyGenerateRules)) {

            for ($i = 0; $i < $countElements; $i++) {

                $categoryPhoto = $this->getRandomCategoryPhoto();

                $arPropertyGenerated = $this->getGeneratedPropertyArray();

                $linkPreviewImage = $this->$dataGenerator->imageUrl(1000, 1000, $categoryPhoto);
                $linkDetailImage = $this->$dataGenerator->imageUrl(1000, 1000, $categoryPhoto);

                $previewPicture = $this->generatePhotoFromLink($linkPreviewImage);
                $detailPicture = $this->generatePhotoFromLink($linkDetailImage);

                $arField = [
                    'IBLOCK_ID' => $this->iblockId,
                    'ACTIVE_FROM' => ConvertTimeStamp($this->$dataGenerator->dateTime('now')->getTimestamp(), "FULL"),
                    'PREVIEW_TEXT' => $this->$dataGenerator->realText(100),
                    'DETAIL_TEXT' => $this->$dataGenerator->realText(500),
                    'PREVIEW_PICTURE' => $previewPicture,
                    'DETAIL_PICTURE' => $detailPicture,
                    'ACTIVE' => 'Y'
                ];

                // Вставляем стандартные свойства
                if (!empty($arPropertyGenerated['STANDART_PROPERTIES'])) {
                    foreach ($arPropertyGenerated['STANDART_PROPERTIES'] as $propCode => $propValue) {
                        $arField[$propCode] = $propValue;
                    }
                }

                // Вставляем свойства
                if (!empty($arPropertyGenerated['PROPERTIES'])) {
                    $arField['PROPERTY_VALUES'] = $arPropertyGenerated['PROPERTIES'];
                }

                $arField['NAME'] = str_replace('$', ($i + 1), $arField['NAME']);

                if (empty($arField['NAME'])) {
                    $arField['NAME'] = $this->$dataGenerator->catchPhrase;
                }

                $arField['CODE'] = \CUtil::translit($arField['NAME'], "ru");

                $el = new \CIBlockElement();

                if ($productId = $el->Add($arField, false, true, false)) {
                    $this->addSuccess($arField);
                } else {
                    $this->addError($el->LAST_ERROR . ' ON LINE ' . __LINE__);

                    if ($this->isStrictMode) {
                        throw new GenerateElementException($el->LAST_ERROR);
                    }
                }
            }
        } else {
            $this->addError('Need set generate rules');

            if ($this->isStrictMode) {
                throw new GenerateElementException('Need set generate rules');
            }
        }


        return $this->getResultData();
    }

    /**
     * Получить обработанный массив сгенерированных данных для элемента
     * 
     * @return array
     */
    protected function getGeneratedPropertyArray(): array {

        $arPropertyGenerated = [
            'PROPERTIES' => [],
            'STANDART_PROPERTIES' => []
        ];

        if (!empty($this->arPropertyGenerateRules)) {

            foreach ($this->arPropertyGenerateRules as $propertyName => $typeGenerator) {

                $isStandartProperties = in_array(str_replace(['*', '='], ['', ''], $propertyName), static::STANDART_PROPERTIES) && ($propertyName[0] == '*' || $propertyName[1] == '*');

                $isDefaultValue = false;

                if ($isStandartProperties) {
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
                        $valuePropety = $this->generateItem($typeGenerator, $arParams);
                    } else {
                        $valuePropety = $typeGenerator;
                    }
                } else {

                    if (sizeof($typeGenerator) == 2) {

                        if (is_numeric($typeGenerator[1]) && is_string($typeGenerator[0])) {

                            $count = $typeGenerator[1];

                            $arParams = explode('(', str_replace(')', '', $typeGenerator[0]));
                            $typeGenerator = array_shift($arParams);

                            $valuePropety = $this->generateItem($typeGenerator, $arParams, $count);
                        }
                    }
                }

                if ($valuePropety !== null) {
                    if ($isStandartProperties) {
                        $arPropertyGenerated['STANDART_PROPERTIES'][str_replace(['*', '='], ['', ''], $propertyName)] = $valuePropety;
                    } else {
                        $arPropertyGenerated['PROPERTIES'][str_replace(['*', '='], ['', ''], $propertyName)] = $valuePropety;
                    }
                }
            }
        }

        return $arPropertyGenerated;
    }

    /**
     * Сгенерировать данные
     * 
     * @param string $typeGenerator - тип генерации
     * @param array $arParams - параметры
     * @param int $count количество генерируемых подэлементов
     * @return array|null сгенерированные данные
     * @throws GeneratorTypeException
     */
    protected function generateItem(string $typeGenerator, array $arParams = [], int $count = 1) {

        $valuePropety = null;

        // @todo Вынести в отдельную сущность, написать через рефлексию генерации где нет параметров
        switch ($typeGenerator) {
            case 'name':
                $valuePropety = $this->$dataGenerator->name;
                break;

            case 'inn':
                $valuePropety = $this->$dataGenerator->inn;
                break;

            case 'kpp':
                $valuePropety = $this->$dataGenerator->kpp;
                break;

            case 'address':
                $valuePropety = $this->$dataGenerator->address;
                break;

            case 'text':
                $valuePropety = $this->$dataGenerator->text;
                break;

            case 'word':
                $valuePropety = $this->$dataGenerator->word;
                break;

            case 'city':
                $valuePropety = $this->$dataGenerator->city;
                break;

            case 'country':
                $valuePropety = $this->$dataGenerator->country;
                break;

            case 'phoneNumber':
                $valuePropety = $this->$dataGenerator->phoneNumber;
                break;

            case 'company':
                $valuePropety = $this->$dataGenerator->company;
                break;

            case 'email':
                $valuePropety = $this->$dataGenerator->email;
                break;

            case 'streetAddress':
                $valuePropety = $this->$dataGenerator->streetAddress;
                break;

            case 'date':
                // @todo Добавить формат
                $valuePropety = $this->$dataGenerator->date;
                break;

            case 'time':
                // @todo Добавить формат
                $valuePropety = $this->$dataGenerator->time;
                break;

            case 'year':
                $valuePropety = $this->$dataGenerator->year;
                break;

            case 'jobTitle':
                $valuePropety = $this->$dataGenerator->jobTitle;
                break;

            case 'lastName':
                $valuePropety = $this->$dataGenerator->lastName;
                break;

            case 'firstName':
                $valuePropety = $this->$dataGenerator->firstName;
                break;

            case 'hexcolor':
                $valuePropety = $this->$dataGenerator->hexcolor;
                break;

            case 'latitude':
                $valuePropety = $this->$dataGenerator->latitude;
                break;

            case 'longitude':
                $valuePropety = $this->$dataGenerator->longitude;
                break;

            case 'image':

                $height = 1000;
                $width = 1000;

                $params = explode(',', $arParams[0]);

                if (!empty($params)) {
                    if (is_numeric($params[0])) {
                        $width = $params[0];
                    }

                    if (is_numeric($params[1])) {
                        $height = $params[1];
                    }
                }

                if ($count > 1) {
                    $valuePropety = [];

                    $num = 0;
                    for ($i = 0; $i < $count; $i++) {
                        $categoryPhoto = $this->getRandomCategoryPhoto();

                        $fileArray = [
                            'VALUE' => $this->generatePhotoFromLink($this->$dataGenerator->imageUrl($width, $height, $categoryPhoto))
                        ];

                        if (!empty($fileArray['VALUE']['tmp_name'])) {
                            $valuePropety['n' . $num] = $fileArray;
                            $num++;
                        }
                    }
                } else {
                    $linkImg = $this->$dataGenerator->imageUrl($width, $height, $categoryPhoto);
                    $valuePropety = $this->generatePhotoFromLink($linkImg);
                }
                break;

            case 'numberBetween':
                // @todo Сделать (1, 100) а не (1)(100)
                $numberFrom = is_numeric($arParams[0]) ? $arParams[0] : 0;
                $numberTo = is_numeric($arParams[1]) ? $arParams[1] : 100;
                $valuePropety = $this->$dataGenerator->numberBetween($numberFrom, $numberTo);
                break;

            case 'randomElement':
                if ($count == 1) {
                    if (!empty($arParams[0])) {
                        $arRand = explode(',', $arParams[0]);

                        $arRand = array_map(function($el) {
                            return trim($el);
                        }, $arRand);

                        $valuePropety = $this->$dataGenerator->randomElement($arRand);
                    }
                } else {

                    $valuePropety = [];
                    $arRand = explode(',', $arParams[0]);

                    for ($i = 0; $i < $count; $i++) {
                        $valuePropety[] = trim($this->$dataGenerator->randomElement($arRand));
                    }
                }
                break;

            case 'realText':
                $length = is_numeric($arParams[0]) ? $arParams[0] : 100;

                if ($count == 1) {
                    $valuePropety = $this->$dataGenerator->realText($length);
                } else {
                    $valuePropety = [];
                    for ($i = 0; $i < $count; $i++) {
                        $valuePropety[] = trim($this->$dataGenerator->realText($length));
                    }
                }

                break;

            default:
                $this->addError('Not found generator type');

                if ($this->isStrictMode) {
                    throw new GeneratorTypeException('Not found generator type');
                }
                break;
        }


        return $valuePropety;
    }

}

/**
 * @version 0.2
 * @todo Добавить исключения
 */
class ElementFishGenerator extends ElementGenerator {

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

        if (\CModule::IncludeModule("iblock")) {
            $dbRes = \CIBlock::GetByID($iblockId);
            if (!$dbRes->GetNext()) {
                throw new SearchIblockException('Указаный инфоблок не найден');
            }
            $this->$dataGenerator = \Faker\Factory::create($localization);
            $this->iblockId = $iblockId;
        } else {
            throw new BitrixRedactionException('Не найдены необходимые для работы библиотеки модули');
        }
    }

}
