<?php
    class JsonController extends Controller {
        public function __construct() {
            parent::__construct();
            header('Access-Control-Allow-Origin: *');
            header('Content-Type: application/json; charset=utf-8');
        }
    }