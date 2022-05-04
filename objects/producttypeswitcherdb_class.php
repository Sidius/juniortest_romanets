<?php
    class ProductTypeSwitcherDB extends ObjectDB implements JsonSerializable {
        protected static $table = "product_type_switcher";

        public function __construct()
        {
            parent::__construct(self::$table);
            $this->add("id", "ValidateID");
            $this->add("title", "ValidateTitle");
            $this->add("unit", "ValidateTitle");
        }

        public static function getAllTypes()
        {
            $select = self::getBaseSelect();
            $data = self::$db->select($select);
            return ObjectDB::buildMultiple(__CLASS__, $data, "id", false);
        }

        public static function getTypeOnID($id)
        {
            $select = self::getBaseSelect();
            $select->where("`id` = ".self::$db->getSQ(), [$id])->limit(1);
            $data = self::$db->select($select);
            return ObjectDB::buildMultiple(__CLASS__, $data, "id", false);
        }

        private static function getBaseSelect()
        {
            $select = new Select(self::$db);
            $select->from(self::$table, "*");
            return $select;
        }

        public function jsonSerialize()
        {
            return [
                'title' => $this->title,
                'unit' => $this->unit,
            ];
        }
    }