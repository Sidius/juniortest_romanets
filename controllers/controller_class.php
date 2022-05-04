<?php
    abstract class Controller extends AbstractController 
    {
        protected $url_active;
        
        public function __construct() 
        {
            parent::__construct(new View(Config::DIR_TMPL), new Message(Config::FILE_MESSAGES));
            $this->url_active = URL::deleteGET(URL::current(), "page");
        }
        
        public function action404() 
        {
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            $properties['response'] = json_encode([
                'count' => 0,
                'response' => 'Not found'
            ]);
            $this->render($properties['response']);
        }
        
        protected function accessDenied() 
        {
            $properties['response'] = json_encode([
                'count' => 0,
                'response' => 'Access denied'
            ]);
            $this->render($properties['response']);
        }
        
        final protected function render($str) 
        {
            $params = [];
            $params["center"] = $str;
            $this->view->render(Config::LAYOUT, $params);
        }
        
        final protected function getOffset($count_on_page) 
        {
            return $count_on_page * ($this->getPage() - 1);
        }
        
        final protected function getPage() 
        {
            $page = ($this->request->page) ? $this->request->page : 1;
            if ($page < 1) 
            {
                $this->notFound();
            }
            return $page;
        }
    }
?>