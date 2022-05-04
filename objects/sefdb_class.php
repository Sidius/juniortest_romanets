<?php
    class SefDB extends ObjectDB 
    {	
        protected static $table = "sef";
        
        public function __construct() 
        {
            parent::__construct(self::$table);
            $this->add("link", "ValidateURI");
            $this->add("alias", "ValidateTitle");
        }
        
        public function loadOnLink($link) 
        {
            return $this->loadOnField("link", $link);
        }
        
        public function loadOnAlias($alias) 
        {
            return $this->loadOnField("alias", $alias);
        }
        
        public static function getAliasOnLink($link)
        {
            $select = new Select(self::$db);
            $select->from(self::$table, ["alias"])->where("`link` = ".self::$db->getSQ(), [$link]);
            return self::$db->selectCell($select);
        }
        
        public static function getLinkOnAlias($alias) 
        {
            $select = new Select(self::$db);
            $select->from(self::$table, ["link"])->where("`alias` = ".self::$db->getSQ(), [$alias]);
            return self::$db->selectCell($select);
        }
    }
?>