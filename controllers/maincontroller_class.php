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

        public function actionAdd()
        {
            $response = true;
            $data = $this->request;

            $product = new ProductDB();
            $product->sku = $data->sku;
            $product->name = $data->name;
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

            $json = json_encode([
                'count' => 0,
                'response' => $product
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
                    } else {
                        $response = false;
                    }
                } catch (Exception $e) {
                    $response = false;
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
    }
?>