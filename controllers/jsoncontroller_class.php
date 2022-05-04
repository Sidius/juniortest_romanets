<?php
    class JsonController extends Controller {
        public function __construct() {
            parent::__construct();
            header('Content-Type: application/json; charset=utf-8');
        }
    }