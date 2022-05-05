<?php
    class ProductUnitsValuesDB extends ObjectDB implements JsonSerializable {
        protected static $table = "product_units_values";

        public function __construct()
        {
            parent::__construct(self::$table);
            $this->add("productSku", "ValidateTitle");
            $this->add("productTypeUnitId", "ValidateID");
            $this->add("value", "ValidateText");
        }

        public static function getAllUnitsValuesOnSku($sku, $post_handling = false)
        {
            $select = self::getBaseSelect();
            $select->where("`productSku` = ".self::$db->getSQ(), [$sku]);
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
            $unit = ProductTypeUnitsDB::getUnitOnID($this->productTypeUnitId)[0];
            $this->productTypeUnit = $unit->title;
        }

        public function jsonSerialize()
        {
            return [
                'value' => (float)$this->value,
                'unit' => $this->productTypeUnit,
                'productTypeUnitId' => (int)$this->productTypeUnitId,
            ];
        }
    }