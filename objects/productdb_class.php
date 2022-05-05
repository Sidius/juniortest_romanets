<?php

    class ProductDB extends ObjectDB implements JsonSerializable {
        protected static $table = "products";

        public function __construct()
        {
            parent::__construct(self::$table);
            $this->add("sku", "ValidateTitle");
            $this->add("name", "ValidateTitle");
            $this->add("price", "ValidatePrice");
            $this->add("productType", "ValidateText");
        }

        public static function getAllProducts($post_handling = false)
        {
            $select = self::getBaseSelect();
            $select->order("sku");
            $data = self::$db->select($select);
            $products = ObjectDB::buildMultiple(__CLASS__, $data, "sku", false);
            if ($post_handling)
            {
                foreach ($products as $product)
                {
                    $product->postHandling();
                }
            }
            return $products;
        }

        public static function getProductOnSKU($sku, $post_handling = false)
        {
            $select = self::getBaseSelect();
            $select->where("`sku` = ".self::$db->getSQ(), [$sku])->limit(1);
            $data = self::$db->select($select);
            $products = ObjectDB::buildMultiple(__CLASS__, $data, "sku", false);
            if ($post_handling)
            {
                foreach ($products as $product)
                {
                    $product->postHandling();
                }
            }
            if ($products && count($products) != 0) {
                return $products[0];
            }
            return null;
        }

        protected function preDelete()
        {
            $unit_values = ProductUnitsValuesDB::getAllUnitsValuesOnSku($this->sku);
            foreach ($unit_values as $unit_value) {
                try {
                    $unit_value->delete();
                } catch (Exception $e) {
                    return false;
                }
            }
            return true;
        }

        public function accessDelete($auth_user)
        {
            return true;
        }

        private static function getBaseSelect()
        {
            $select = new Select(self::$db);
            $select->from(self::$table, "*");
            return $select;
        }

        private function postHandling()
        {
            $type = ProductTypeSwitcherDB::getTypeOnID($this->productType)[0];
            $unit_values = ProductUnitsValuesDB::getAllUnitsValuesOnSku($this->sku, true);
            $this->productType = $type->title;
            $this->unit = $type->unit;
            $this->attributes = $unit_values;
        }

        public function jsonSerialize()
        {
            return [
                'sku' => $this->sku,
                'name' => $this->name,
                'price' => (float)$this->price,
                'productType' => $this->productType,
                'unit' => $this->unit,
                'attributes' => $this->attributes
            ];
        }
    }