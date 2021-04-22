<?

namespace GGrach\FishGenerator\Generators;

use GGrach\FishGenerator\Exceptions\GeneratePhotoException;

/**
 * @todo Add https://dummyimage.com/
 * @todo https://loremflickr.com/ 
 * @todo https://loremipsum.io/ru/21-of-the-best-placeholder-image-generators/
 */


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
