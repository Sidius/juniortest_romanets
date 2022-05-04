<?php
    class ProductTypeUnitsDB extends ObjectDB {
        protected static $table = "product_type_units";

        public function __construct()
        {
            parent::__construct(self::$table);
            $this->add("id", "ValidateID");
            $this->add("productTypeSwitcherId", "ValidateID");
            $this->add("title", "ValidateTitle");
        }

        public static function getUnitOnID($id, $post_handling = false)
        {
            $select = self::getBaseSelect();
            $select->where("`id` = ".self::$db->getSQ(), [$id])->limit(1);
            $data = self::$db->select($select);
            $unitsValues = ObjectDB::buildMultiple(__CLASS__, $data, "id", false);
            if ($post_handling)
            {
                foreach ($unitsValues as $unitsValue)
                {
                    $unitsValue->postHandling();
                }
            }
            return $unitsValues;
        }

        private static function getBaseSelect()
        {
            $select = new Select(self::$db);
            $select->from(self::$table, "*");
            return $select;
        }

        private function postHandling()
        {
            $type = ProductTypeSwitcherDB::getTypeOnID($this->productTypeSwitcherId)[0];
            $this->productType = $type->title;
        }
    }