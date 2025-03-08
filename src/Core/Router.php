<?php

namespace Core;
use App\Models\User;

class Router
{
    private array $routes = [];

    public function add(string $serverMethod, string|array $path, array $handler): void
    {
        if(is_array($path)){
            foreach($path as $p){
                if(is_string($p)) $this->routes[strtoupper($serverMethod)][$p] = $handler;
            }
        }else{
            $this->routes[strtoupper($serverMethod)][$path] = $handler;
        }
    }

    public function dispatch(string $uri, User $user=null): void
    {
        $serverMethod = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($uri, PHP_URL_PATH);
        if (isset($this->routes[$serverMethod][$uri])) {
            [$class, $method] = $this->routes[$serverMethod][$uri];
            $controller = new $class($user);
            $controller->$method();
        } else {
            http_response_code(404);
            echo "404 Not Found";
        }
    }
}

?>