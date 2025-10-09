<?php

namespace App\Core;

class Router
{
    private static array $routes = [];
    private static array $middleware = [];

    public static function get(string $path, $handler): Route
    {
        return self::addRoute('GET', $path, $handler);
    }

    public static function post(string $path, $handler): Route
    {
        return self::addRoute('POST', $path, $handler);
    }

    public static function put(string $path, $handler): Route
    {
        return self::addRoute('PUT', $path, $handler);
    }

    public static function delete(string $path, $handler): Route
    {
        return self::addRoute('DELETE', $path, $handler);
    }

    private static function addRoute(string $method, string $path, $handler): Route
    {
        $route = new Route($method, $path, $handler);
        self::$routes[] = $route;
        return $route;
    }

    public static function middleware(string $name, callable $callback): void
    {
        self::$middleware[$name] = $callback;
    }

    public static function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path = $request->path();

        foreach (self::$routes as $route) {
            if ($route->matches($method, $path)) {
                // Apply middleware
                foreach ($route->getMiddleware() as $middlewareName) {
                    if (isset(self::$middleware[$middlewareName])) {
                        $result = call_user_func(self::$middleware[$middlewareName], $request);
                        if ($result instanceof Response) {
                            return $result;
                        }
                    }
                }

                // Extract route parameters
                $params = $route->extractParams($path);
                $request->setParams($params);

                // Execute route handler
                $handler = $route->getHandler();
                
                if (is_array($handler) && count($handler) === 2) {
                    [$controller, $method] = $handler;
                    $controllerInstance = new $controller();
                    
                    // Pass route parameters to controller method
                    if (!empty($params)) {
                        return call_user_func_array([$controllerInstance, $method], array_values($params));
                    } else {
                        return $controllerInstance->$method();
                    }
                } elseif (is_callable($handler)) {
                    return call_user_func($handler, $request);
                }
            }
        }

        // 404 Not Found
        return new Response('404 Not Found', 404);
    }

    public static function getMiddleware(string $name): ?callable
    {
        return self::$middleware[$name] ?? null;
    }
}
