<?php
    class MainController extends JsonController
    {
        public function actionIndex() 
        {
            $products = ProductDB::getAllProducts(true);

            $json = json_encode([
                'count' => count($products),
                'response' => $products
            ]);

            $this->render($this->renderData(["response" => $json], "index_json"));
        }

        public function actionAddProduct()
        {
            $response = true;
            $data = $this->request;

            $product = new ProductDB();
            if ($data->sku) {
                $product_check = ProductDB::getProductOnSKU($data->sku);
                if (!$product_check) {
                    $product->sku = $data->sku;
                } else {
                    $response = false;
                }
            } else {
                $response = false;
            }
            if ($data->name) {
                $product->name = $data->name;
            } else {
                $response = false;
            }
            if (is_numeric($data->price)) {
                $product->price = (float)$data->price;
            } else {
                $response = false;
            }
            if (is_numeric($data->productTypeID)) {
                $id = (int)$data->productTypeID;
                $type = ProductTypeSwitcherDB::getTypeOnID($id);
                if ($type) {
                    $product->productType = $id;
                } else {
                    $response = false;
                }
            } else {
                $response = false;
            }

            if ($response) {
                $response = $product->save('sku');
            }

            $json = json_encode([
                'response' => $response
            ]);

            $this->render($this->renderData(["response" => $json], "index_json"));
        }

        public function actionAddProductAttribute()
        {
            $response = true;
            $data = $this->request;

            $unitValue = new ProductUnitsValuesDB();
            if ($data->productSku) {
                $product = ProductDB::getProductOnSKU($data->productSku);
                if ($product) {
                    $unitValue->productSku = $data->productSku;
                } else {
                    $response = false;
                }
            } else {
                $response = false;
            }
            if ($data->productTypeUnitId && is_numeric($data->productTypeUnitId)) {
                $id = (int)$data->productTypeUnitId;
                $unit = ProductTypeUnitsDB::getUnitOnID($id, true);
                if ($unit) {
                    $unitValue->productTypeUnitId = $id;
                } else {
                    $response = false;
                }
            } else {
                $response = false;
            }
            if ($data->value && is_numeric($data->value)) {
                $unitValue->value = (float)$data->value;
            } else {
                $response = false;
            }

            if ($response && !$unitValue->hasValue()) {
                $response = $unitValue->save();
            } else {
                $response = false;
            }

            $json = json_encode([
                'response' => $response
            ]);

            $this->render($this->renderData(["response" => $json], "index_json"));
        }

        public function actionDelete()
        {
            $sku = $this->request->sku;
            $response = false;
            if ($sku) {
                $product = ProductDB::getProductOnSKU($sku);
                try {
                    if ($product) {
                        $response = $product->delete('sku');
                    }
                } catch (Exception $e) {
                }
            }

            $json = json_encode([
                'response' => $response
            ]);

            $this->render($this->renderData(["response" => $json], "index_json"));
        }

        public function actionGetAllTypes()
        {
            $types = ProductTypeSwitcherDB::getAllTypes();

            $json = json_encode([
                'count' => count($types),
                'response' => $types
            ]);

            $this->render($this->renderData(["response" => $json], "index_json"));
        }

        public function actionGetProductUnits()
        {
            $data = $this->request;
            $types = null;
            if (!$data->type_id) {
                $types = ProductTypeUnitsDB::getAllUnits(true);
            } else {
                $types = ProductTypeUnitsDB::getUnitsOnTypeID($data->type_id, true);
            }

            $json = json_encode([
                'count' => count($types),
                'response' => $types
            ]);

            $this->render($this->renderData(["response" => $json], "index_json"));
        }
    }
?>