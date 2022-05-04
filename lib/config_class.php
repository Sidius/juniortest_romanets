<?php
    abstract class Config 
    {
        const SITENAME = "juniortest.romanets.local";
        const SECRET = "DGLJDG5";
        const ADDRESS = "http://juniortest.romanets.local";
        const ADM_NAME = "Павел Романец";
        const ADM_EMAIL = "admin@romanets.ru";
        const LOCAL_RU = "ru_RU";
        
        const API_KEY = "DKEL39DL";
        
        const DB_HOST = "localhost";
        const DB_USER = "root";
        const DB_PASSWORD = "";
        const DB_NAME = "juniortest_romanets_db";
        const DB_PREFIX = "";
        const DB_SYM_QUERY = "?";

        const DIR_TMPL = "/domains/".self::SITENAME."/tmpl/";
        
        const LAYOUT = "main_json";
        const FILE_MESSAGES = "/domains/".self::SITENAME."/text/messages.ini";
        
        const FORMAT_DATE = "%d.%m.%Y %H:%M:%S";
        
        const SEF_SUFFIX = "";
        
        const DEFAULT_AVATAR = "default.png";
    }
?>