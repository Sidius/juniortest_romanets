<?php
    abstract class AbstractObjectDB 
    {   
        const TYPE_TIMESTAMP = 1;
        const TYPE_IP = 2;
        
        private static $types = [self::TYPE_TIMESTAMP, self::TYPE_IP];
        protected static $db = null;
        
        private $format_date = "";
        
        private $id = null;
        private $properties = [];
        
        protected $table_name = "";
        
        public function __construct($table_name, $format_date) 
        {
            $this->table_name = $table_name;
            $this->format_date = $format_date;
        }
        
        public static function setDB($db) 
        {
            self::$db = $db;
        }
        
        public function load($id, $id_name = "id")
        {
            $id = (int) $id;
            if ($id < 0) 
            {
                return false;
            }
            $select = new Select(self::$db);
            $select = $select->from($this->table_name, $this->getSelectFields())->where("`id` = ".self::$db->getSQ(), [$id]);
            $row = self::$db->selectRow($select);
            if (!$row) 
            {
                return false;
            }
            if ($this->init($row, $id_name))
            {
                return $this->postLoad();
            }
        }
        
        public function init($row, $id_name = "id")
        {
            foreach ($this->properties as $key => $value) 
            {
                $val = $row[$key];
                switch ($value["type"]) 
                {
                    case self::TYPE_TIMESTAMP:
                        if (!is_null($val)) 
                        {
                            $val = strftime($this->format_date, $val);
                        }
                        break;
                    case self::TYPE_IP:
                        if (!is_null($val)) 
                        {
                            $val = long2ip($val);
                        }
                        break;
                }
                $this->properties[$key]["value"] = $val;
            }
            $this->id = $row[$id_name];
            return $this->postInit();
        }

        public function isSaved($id_name = "id", $table = null, $id = null)
        {
            if ($table && $id) {
                $select = new Select(self::$db);
                $select->from($table, "*");
                $select->where("`$id_name` = ".self::$db->getSQ(), [$id]);
                $data = self::$db->select($select);
                if ($data) {
                    return true;
                }
                return false;
            }
            return $this->getID($id_name) > 0;
        }
        
        public function getID($id_name = "id")
        {
            return $this->$id_name;
        }
        
        public function save($id_name = "id")
        {
            $update = $this->isSaved($id_name, $this->table_name, $this->getID($id_name));
            if ($update) 
            {
                $commit = $this->preUpdate();
            }
            else 
            {
                $commit = $this->preInsert();
            }
            if (!$commit) 
            {
                return false;
            }
            $row = [];
            foreach ($this->properties as $key => $value) 
            {
                switch ($value["type"]) 
                {
                    case self::TYPE_TIMESTAMP:
                        if (!is_null($value["value"])) 
                        {
                            $value["value"] = strtotime($value["value"]);
                        }
                        break;
                    case self::TYPE_IP:
                        if (!is_null($value["value"])) 
                        {
                            $value["value"] = ip2long($value["value"]);
                        }
                        break;
                }
                $row[$key] = $value["value"];
            }
            if (count($row) > 0) 
            {
                if ($update) 
                {
                    $success = self::$db->update($this->table_name, $row, "`$id_name` = ".self::$db->getSQ(), [$this->getID()]);
                    if (!$success) 
                    {
                        throw new Exception();
                    }
                }
                else 
                {
                    $this->id = self::$db->insert($this->table_name, $row);
                    if (!$this->id) 
                    {
                        throw new Exception();
                    }
                }
            }
            if ($update) 
            {
                return $this->postUpdate();
            }
            return $this->postInsert();
        }
        
        public function delete($id_name = 'id')
        {
            if (!$this->isSaved($id_name))
            {
                return false;
            }
            if (!$this->preDelete()) 
            {
                return false;
            }
            $success = self::$db->delete($this->table_name, "`$id_name` = ".self::$db->getSQ(), [$this->getID()]);
            if (!$success) 
            {
                throw new Exception();
            }
            $this->id = null;
            return $this->postDelete();
        }
        
        public function __set($name, $value)
        {
            if (array_key_exists($name, $this->properties)) 
            {
                $this->properties[$name]["value"] = $value;
                return true;
            }
            else 
            {
                $this->$name = $value;
            }
        }
            
        public function __get($name) 
        {
            if ($name == "id") 
            {
                return $this->getID();
            }
            return array_key_exists($name, $this->properties) ? $this->properties[$name]["value"] : null;
        }
        
        public static function buildMultiple($class, $data, $id_name = "id", $is_abstract = true)
        {
            $ret = [];
            
            if (!class_exists($class)) 
            {
                throw new Exception();
            }
            
            $test_obj = new $class();
            if (!$test_obj instanceof AbstractObjectDB) 
            {
                throw new Exception();
            }
            foreach ($data as $row) 
            {
                $obj = new $class();
                $obj->init($row, $id_name);
                if ($is_abstract) {
                    $ret[$obj->getID()] = $obj;
                } else {
                    $ret[] = $obj;
                }
            }
            return $ret;
        }
        
        public static function getAll($count = false, $offset = false) 
        {
            $class = get_called_class();
            return self::getAllWithOrder($class::$table, $class, "id", true, $count, $offset);
        }
        
        public static function getCount() 
        {
            $class = get_called_class();
            return self::getCountOnWhere($class::$table, false, false);
        }
        
        public static function getAllOnField($table_name, $class, $field, $value, $order = false, $ask = true, $count = false, $offset = false)
        {
            return self::getAllOnWhere($table_name, $class, "`$field` = ".self::$db->getSQ(), [$value], $order, $ask, $count, $offset);
        }
        
        protected static function getCountOnField($table_name, $field, $value)
        {
            return self::getCountOnWhere($table_name, "`$field` = ".self::$db->getSQ(), [$value]);
        }
        
        protected static function getCountOnWhere($table_name, $where = false, $values = false) 
        {
            $select = new Select();
            $select->from($table_name, ["COUNT(id)"]);
            if ($where) 
            {
                $select->where($where, $values);
            }
            return self::$db->selectCell($select);
        }
        
        protected static function getAllWithOrder($table_name, $class, $order = false, $ask = true, $count = false, $offset = false) 
        {
            return self::getAllOnWhere($table_name, $class, false, false, $order, $ask, $count, $offset);
        }
        
        protected static function getAllOnWhere($table_name, $class, $where = false, $values = false, $order = false, $ask = true, $count = false, $offset = false) 
        {
            $select = new Select();
            $select->from($table_name, "*");
            if ($where) 
            {
                $select->where($where, $values);
            }
            if ($order) 
            {
                $select->order($order, $ask);
            }
            else 
            {
                $select->order("id");
            }
            if ($count) 
            {
                $select->limit($count, $offset);
            }
            $data = self::$db->select($select);
            return AbstractObjectDB::buildMultiple($class, $data);
        }
        
        protected static function addSubObject($data, $class, $field_out, $field_in) 
        {
            $ids = [];
            
            foreach ($data as $value) 
            {
                $ids[] = self::getComplexValue($value, $field_in);
            }
            if (count($ids) == 0) 
            {
                return [];
            }
            $new_data = $class::getAllOnIDs($ids);
            if (count($new_data) == 0) 
            {
                return $data;
            }
            foreach ($data as $id => $value) 
            {
                if (isset($new_data[self::getComplexValue($value, $field_in)])) 
                {
                    $data[$id]->$field_out = $new_data[self::getComplexValue($value, $field_in)];
                }
                else 
                {
                    $value->$field_out = null;
                }
            }
            return $data;
        }
        
        protected static function getComplexValue($obj, $field) 
        {
            if (strpos($field, "->") !== false) 
            {
                $field = explode("->", $field);
            }
            if (is_array($field))
            {
                $value = $obj;
                foreach ($field as $f) 
                {
                    $value = $value->{$f};
                }
            }
            else 
            {
                $value = $obj->$field;
            }
            return $value;
        }
        
        public static function getAllOnIDs($ids) 
        {
            return self::getAllOnIDsField($ids, "id");
        }
        
        public static function getAllOnIDsField($ids, $field) 
        {
            $class = get_called_class();
            $select = new Select();
            $select->from($class::$table, "*")->whereIn($field, $ids);
            $data = self::$db->select($select);
            return AbstractObjectDB::buildMultiple($class, $data);
        }
        
        protected function loadOnField($field, $value, $id_name = "id")
        {
            $select = new Select();
            $select->from($this->table_name, "*")->where ("`$field` = ".self::$db->getSQ(), [$value]);
            $row = self::$db->selectRow($select);
            if ($row) 
            {
                if ($this->init($row, $id_name))
                {
                    return $this->postLoad();
                }
            }
            return false;
        }
        
        protected function add($field, $validator, $type = null, $default = null) 
        {
            $this->properties[$field] = ["value" => $default, "validator" => $validator, "type" => in_array($type, self::$types)? $type : null];
        }
        
        protected function preInsert() 
        {
            return $this->validate();
        }
        
        protected function postInsert() 
        {
            return true;
        }
        
        protected function preUpdate() 
        {
            return $this->validate();
        }
        
        protected function postUpdate() 
        {
            return true;
        }
        
        protected function preDelete() 
        {
            return true;
        }
        
        protected function postDelete() 
        {
            return true;
        }
        
        protected function postInit() 
        {
            return true;
        }
        
        protected function preValidate() 
        {
            return true;
        }
        
        protected function postValidate() 
        {
            return true;
        }
        
        protected function postLoad() 
        {
            return true;
        }
        
        public function getDate($date = false) 
        {
            if (!$date) 
            {
                $date = time();
            }
            $date = strtotime($date);
            return strftime($this->format_date, $date);
        }
        
        protected static function getDay($date = false) 
        {
            $date = strtotime($date);
            return date("d", $date);
        }
        
        protected static function searchObjects($select, $class, $fields = [], $words, $min_len) 
        {
            $words = mb_strtolower($words);
            $words = preg_replace("/ {2,}/", " ", $words);
            if ($words == "") 
            {
                return [];
            }
            $array_words = explode(" ", $words);
            $temp = [];
            foreach ($array_words as $value) 
            {
                if (strlen($value) >= $min_len) 
                {
                    $temp[] = $value;
                }
            }
            $array_words = $temp;
            if (count($array_words) == 0)
            {
                return [];
            }
            foreach ($array_words as $value) 
            {
                $where = "";
                $params = [];
                for ($i = 0; $i < count($fields); $i++) 
                {
                    $where .= "`".$fields[$i]."` LIKE ".self::$db->getSQ();
                    $params[] = "%$value%";
                    if (($i + 1) != count($fields)) 
                    {
                        $where .= "OR";
                    }
                }
                $select->where("($where)", $params, true);
            }
            $results = self::$db->select($select);
            if (!$results) 
            {
                return [];
            }
            $results = ObjectDB::buildMultiple($class, $results);
            foreach ($results as $result) 
            {
                for ($j = 0; $j < count($fields); $j++) 
                {
                    $fl = $fields[$j];
                    $result->$fl = mb_strtolower(strip_tags($result->$fl));
                }
                $data[$result->id] = $result;
                $data[$result->id]->relevant = self::getRelevantForSearch($result, $fields, $array_words);
            }
            uasort($data, ["AbstractObjectDB", "compareRelevant"]);
            return $data;
        }
        
        private static function getRelevantForSearch($result, $fields, $array_words) 
        {
            $relevant = 0;
            for ($i = 0; $i < count($fields); $i++)
            {
                for ($j = 0; $j < count($array_words); $j++)
                {
                    $fl = $fields[$i];
                    $relevant += substr_count($result->$fl, $array_words[$j]);
                }
            }
            return $relevant;
        }
        
        private static function compareRelevant($value_1, $value_2)
        {
            return $value_1->relevant < $value_2->relevant;
        }
        
        protected function getIP() 
        {
            return $_SERVER["REMOTE_ADDR"];
        }
        
        protected static function hash($str, $secret = "") 
        {
            return md5($str.$secret);
        }
        
        protected function getKey() 
        {
            return uniqid();
        }
        
        private function getSelectFields() 
        {
            $fields = array_keys($this->properties);
            array_push($fields, "id");
            return $fields;
        }
        
        private function validate() 
        {
            if (!$this->preValidate()) 
            {
                throw new Exception();
            }
            $v = [];
            $errors = [];
            foreach ($this->properties as $key => $value) 
            {
                $v[$key] = new $value["validator"]($value["value"]);
            }
            foreach ($v as $key => $validator) 
            {
                if (!$validator->isValid()) 
                {
                    $errors[$key] = $validator->getErrors();
                }
            }
            if (count($errors) == 0) 
            {
                if (!$this->postValidate()) 
                {
                    throw new Exception();
                }
                return true;
            }
            else 
            {
                throw new ValidatorException($errors);
            }
        }
    }
?>