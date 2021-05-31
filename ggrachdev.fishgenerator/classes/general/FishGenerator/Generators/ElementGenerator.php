<?

namespace GGrach\FishGenerator\Generators;

use GGrach\FishGenerator\Exceptions\GenerateElementException;
use GGrach\FishGenerator\Exceptions\GeneratorTypeException;
use GGrach\FishGenerator\PropertyRulesElementFilter;
use GGrach\FishGenerator\Parser\RuleGenerationParser;

/**
 * Логика генерации элемента
 */
class ElementGenerator extends PropertyRulesElementFilter {

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

                $arValidPropertiesForGeneration = RuleGenerationParser::parse($this->arPropertyGenerateRules, $this);

                $linkPreviewImage = 'https://loremflickr.com/1000/1000/'.$categoryPhoto.'?salt='. \uniqid();
                $linkDetailImage = 'https://loremflickr.com/1000/1000/'.$categoryPhoto.'?salt='. \uniqid();

                $previewPicture = $this->generatePhotoFromLink($linkPreviewImage);
                $detailPicture = $this->generatePhotoFromLink($linkDetailImage);

                $arField = [
                    'IBLOCK_ID' => $this->iblockId,
                    'ACTIVE_FROM' => ConvertTimeStamp($this->dataGenerator->dateTime('now')->getTimestamp(), "FULL"),
                    'PREVIEW_TEXT' => $this->dataGenerator->realText(100),
                    'DETAIL_TEXT' => $this->dataGenerator->realText(500),
                    'PREVIEW_PICTURE' => $previewPicture,
                    'DETAIL_PICTURE' => $detailPicture,
                    'ACTIVE' => 'Y'
                ];

                // Вставляем стандартные свойства
                if (!empty($arValidPropertiesForGeneration['STANDART_PROPERTIES'])) {
                    foreach ($arValidPropertiesForGeneration['STANDART_PROPERTIES'] as $propCode => $propValue) {
                        $arField[$propCode] = $propValue;
                    }
                }

                // Вставляем свойства
                if (!empty($arValidPropertiesForGeneration['PROPERTIES'])) {
                    $arField['PROPERTY_VALUES'] = $arValidPropertiesForGeneration['PROPERTIES'];
                }

                $arField['NAME'] = str_replace('$', ($i + 1), $arField['NAME']);

                if (empty($arField['NAME'])) {
                    $arField['NAME'] = $this->dataGenerator->catchPhrase;
                }

                $arField['CODE'] = \CUtil::translit($arField['NAME'], "ru");

                $el = new \CIBlockElement();
                $productId = $el->Add($arField, false, true, false);

                if ($productId) {
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
     * Сгенерировать данные
     * 
     * @param string $typeGenerator - тип генерации
     * @param array $arParams - параметры
     * @param int $count количество генерируемых подэлементов
     * @return array|null сгенерированные данные
     * @throws GeneratorTypeException
     */
    public function generateItem(string $typeGenerator, array $arParams = [], int $count = 1) {

        $valuePropety = null;

        if (!empty($arParams[0])) {
            $params = explode(',', $arParams[0]);
        }
        else
        {
            $params = [];
        }

        // @todo Вынести в отдельную сущность, написать через рефлексию генерации где нет параметров
        switch ($typeGenerator) {
            case 'image':

                $height = 1000;
                $width = 1000;

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
                            'VALUE' => $this->generatePhotoFromLink(
                                'https://loremflickr.com/'.$width.'/'.$height.'/'.$categoryPhoto.'?salt='. \uniqid()
                            )
                        ];

                        if (!empty($fileArray['VALUE']['tmp_name'])) {
                            $valuePropety['n' . $num] = $fileArray;
                            $num++;
                        }
                    }
                } else {
                    $linkImg = 'https://loremflickr.com/'.$width.'/'.$height.'/'.$categoryPhoto.'?salt='. \uniqid();
                    $valuePropety = $this->generatePhotoFromLink($linkImg);
                }
                break;

            case 'randomElement':
                if ($count == 1) {
                    if (!empty($arParams[0])) {
                        $arRand = explode(',', $arParams[0]);

                        $arRand = array_map(function ($el) {
                            return trim($el);
                        }, $arRand);

                        $valuePropety = $this->dataGenerator->randomElement($arRand);
                    }
                } else {

                    $valuePropety = [];
                    $arRand = explode(',', $arParams[0]);

                    for ($i = 0; $i < $count; $i++) {
                        $valuePropety[] = trim($this->dataGenerator->randomElement($arRand));
                    }
                }
                break;

            case 'realText':
                $length = is_numeric($arParams[0]) ? $arParams[0] : 100;

                if ($count == 1) {
                    $valuePropety = $this->dataGenerator->realText($length);
                } else {
                    $valuePropety = [];
                    for ($i = 0; $i < $count; $i++) {
                        $valuePropety[] = trim($this->dataGenerator->realText($length));
                    }
                }

                break;

            default:
                try {
                    if ($count == 1) {
                        $valuePropety = \call_user_func_array([$this->dataGenerator, $typeGenerator], $params);
                    } else {
                        $valuePropety = [];
                        for ($i = 0; $i < $count; $i++) {
                            $valuePropety[] = \call_user_func_array([$this->dataGenerator, $typeGenerator], $params);
                        }
                    }
                } catch (Exception $ex) {
                    $this->addError($ex->getMessage());

                    if ($this->isStrictMode) {
                        throw new GeneratorTypeException($ex->getMessage());
                    }
                }
                break;
        }


        return $valuePropety;
    }

}
