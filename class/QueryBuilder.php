<?php


class QueryBuilder
{
    private PDO $db;
    private $results;
    private int $CountRow;
    private bool $error;

    private const QB_WHERE = 0;
    private const QB_SET = 1;


    public function __construct($db)  {
        $this->db = $db;
    }

    /**
     * Функция выполняет запрос.
     * @param string $query
     * Запрос к бд
     * @param array $data
     * Масситв подставляемых данных
     * @param int $type
     * Тип возвращаемых данных PDO::FETCH_
     * @return void
     */
    public function query(string $query,array $data = [],$type=PDO::FETCH_ASSOC) :void {

        $this->error = false;
        $this->errorCode = false;

        $statements = $this->db->prepare($query);

        if($statements->execute($data)) {

            $this->results = $statements->fetchAll($type);
            $this->CountRow = $statements->rowCount();
        } else {
            $this->errorCode = $statements->errorCode();
            $this->error = true;
        }
    }

    /**
     * Генерирует случайное имя указанной длины
     * @param int $len
     * @return string
     */
    private static function getRandName(int $len = 5) :string {

        $strChars = "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM";
        $countChars = strlen($strChars);
        $strKey = "";
        while ($len) {
            $len--;
            $strKey .= $strChars[rand(0,$countChars)];
        }

        return $strKey;
    }

    /**
     * Функция для форматирования данных параметра запроса.
     * @param array $condition
     * Получает ассоциативный массив 'название поля таблицы'=>'значение'
     * @param int $type
     * Для подстановки ключевых слов
     * @param array $paramsNewSetKey
     * Массив для того что бы вернуть ключи с параметрами.
     * @return string
     * Возвращает дополненную строку запроса.
     */
    private static function formatCondition(array $condition,$type = self::QB_WHERE, &$paramsNewSetKey = []) :string {
        $strKey = '';
        foreach ($condition as $key => $value) {
            $newKey = $key;
            //Что бы избежать конфликта имён в массиве.
            if($type == self::QB_SET) {
                $newKey .= "_" . self::getRandName(5);
                $paramsNewSetKey = array_merge($paramsNewSetKey,[$newKey => $value]);
            }

            $strKey .= " {$key}=:{$newKey},";
        }

        return $strKey = ($type == self::QB_WHERE ?" WHERE ":" SET ") . mb_substr($strKey, 0, -1);
    }

    /**
     * Функция возвращает результат полученных данных если последний запрос был успешный.
     * @return array
     */
    public function Results(): array {
        if(!$this->error) {
            return $this->results;
        }
        return [];
    }

    /**
     * Возвращает количество затронутых записей.
     * @return int
     */
    public function CountRow() :int {
        if(!$this->error)
            return $this->CountRow;
        return 0;
    }

    /**
     * Функция для получения выборки из бд
     * @param string $table
     * Имя таблицы
     * @param string $param
     * Список параметров в строке через запятую которые следует получить. (Не лучший способ)
     * @param array $condition
     * Условия по которым мы получаем данные. Если не чего не передано - получаем все.
     * @param int $type
     * Тип возвращаемых данных PDO::FETCH_
     * @return bool
     */
    public function select(string $table,string $param = '*', array $condition = [],$type = PDO::FETCH_ASSOC) :bool {

        $query = "SELECT {$param} FROM {$table}";

        if($condition) {
            $query .= self::formatCondition($condition);
        }
        $query .= ";";

        $this->query($query,$condition,$type);
        return !$this->error;
    }

    /**
     * Функция для удаление записи из бд (одной или нескольких).
     * @param string $table
     * Имя таблицы
     * @param array $condition
     * Условия по которым мы получаем данные. Если не чего не передано - получаем все.
     * @return bool
     */
    public function delete(string $table, array $condition = []) :bool {

        $query = "DELETE FROM {$table}";
        if($condition) {
            $query .= self::formatCondition($condition);
        }
        $query .= ";";

        $this->query($query,$condition);
        return !$this->error;
    }

    /**
     * Функция для обновления записей в бд
     * @param $table
     * Имя таблицы
     * @param array $data
     * Параметры которые нужно обновить в виде асоциативного массива.
     * @param array $condition
     * Условия по которым мы обновляем данные. Если не чего не передано - обновляем все.
     * @return bool
     */
    public function update($table,array $data = [], array $condition = []) :bool {

        $query = "UPDATE {$table}";
        if($data)
            $query .= self::formatCondition($data,self::QB_SET,$condition);
        if($condition)
            $query .= self::formatCondition($data);

        $query .= ";";

        $this->query($query,$condition);
        return !$this->error;
    }

    /**
     * Функция для обновления данных из бд
     * @param $table
     * Имя таблицы
     * @param array $data
     * Условия по которым мы получаем данные. Параметр обязательный.
     * @return bool
     */
    public function insert($table, array $data) :bool {

        $query = "INSERT INTO {$table} (";
        $queryValue = "";

        foreach ($data as $key => $value) {
            $query .= "{$key},";
            $queryValue .= ":{$key},";
        }

        $query = mb_substr($query, 0, -1) . ") value (" . mb_substr($queryValue,0,-1) . ");";

        $this->query($query,$data);
        return $this->error;
    }
}