<?php
    abstract class Config 
    {
        const LOCAL_RU = "ru_RU";
        
        const DB_HOST = "localhost";
        const DB_USER = "root";
        const DB_PASSWORD = "root";
        const DB_NAME = "juniortest_romanets_db";
        const DB_PREFIX = "";
        const DB_SYM_QUERY = "?";

        const DIR_TMPL = DOCUMENT_ROOT."/tmpl/";
        
        const LAYOUT = "main_json";
        const FILE_MESSAGES = DOCUMENT_ROOT."/text/messages.ini";
        
        const FORMAT_DATE = "%d.%m.%Y %H:%M:%S";
        
        const SEF_SUFFIX = "";
    }
?>