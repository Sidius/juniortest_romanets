<?php
	mb_internal_encoding("UTF-8");
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	
	set_include_path(get_include_path().PATH_SEPARATOR."core".PATH_SEPARATOR."lib".PATH_SEPARATOR."objects".PATH_SEPARATOR."validator".PATH_SEPARATOR."controllers".PATH_SEPARATOR."modules");
	spl_autoload_extensions("_class.php");
	spl_autoload_register();

	define("DOCUMENT_ROOT", $_SERVER['DOCUMENT_ROOT']);
	define("FILE_MESSAGES", $_SERVER['DOCUMENT_ROOT']."/text/messages.ini");
    define("SITE_NAME", $_SERVER['HTTP_HOST']);
    define("ROOT", dirname(__DIR__));
    define("ADDRESS", "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}");

	AbstractObjectDB::setDB(DataBase::getDBO());
?>