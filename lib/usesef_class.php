<?php
    class UseSEF 
    {	
        public static function replaceSEF($link, $address = "") 
        {
            if ((strpos($link, "//") !== false) && (strpos($link, ADDRESS) === false)) 
            {
                return $link;
            }
            if (strpos($link, ADDRESS) === 0) 
            {
                $link = substr($link, mb_strlen(ADDRESS));
            }
            if ($link === "/") 
            {
                return $address.$link;
            }
            if (preg_match("/^\/\?page=/i", $link, $matches)) 
            {
                return "/page-";
            }
            $alias = SefDB::getAliasOnLink($link);
            if ($alias) 
            {
                $link = $address."/".$alias.Config::SEF_SUFFIX;
            }
            else 
            {
                $data = parse_url($link);
                $alias = SefDB::getAliasOnLink($data["path"]);
                if ($alias) 
                {
                    $link = $address."/".$alias.Config::SEF_SUFFIX."?".$data["query"];
                }
            }
            return $link;
        }
        
        public static function getRequest($uri) 
        {
            if (strpos($uri, ADDRESS) !== false)
            {
                $uri = substr($uri, strlen(ADDRESS));
            }
            if ($uri === "/") 
            {
                return $uri;
            }
            $uri = substr($uri, 1);
            $uri = str_replace(Config::SEF_SUFFIX, "", $uri);
            if (preg_match("/^page-(\d+)$/i", $uri, $matches)) 
            {
                return "/?page=".$matches[1];
            }
            $result = SefDB::getLinkOnAlias($uri);
            if (!$result) 
            {
                $uri = substr($uri, 0, strpos($uri, "?"));
                $result = SefDB::getLinkOnAlias($uri);
            }
            if ($result) 
            {
                return $result;
            }
            return false;
        }	
    }
?>