<?

namespace GGrach\FishGenerator;

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
