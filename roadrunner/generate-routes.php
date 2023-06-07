<?php

require 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

// Load YAML file and parse it
$yaml = Yaml::parseFile('config/openapi.yaml');

$routesPhp = "<?php\n\n";
// $routesPhp .= "use Psr\Http\Message\ServerRequestInterface;\n";
// $routesPhp .= "use Psr\Http\Message\ResponseInterface;\n";
// $routesPhp .= "use League\Route\RouteCollection;\n\n";

// $routesPhp .= '$router = new RouteCollection();' . "\n\n";

// Loop through paths in the spec
foreach ($yaml['paths'] as $path => $methods) {
    foreach ($methods as $method => $details) {
        // For this example, we assume that each route has a corresponding handler class
        // The class name is derived from the operationId
        // You need to adjust this according to your application structure
        $handlerClass = 'App\\Controller\\' . ucfirst($details['operationId']) . 'Controller';

        $routesPhp .= "\$router->map('". strtoupper($method) . "', '" . $path . "', '" . $handlerClass . "::" . strtolower($method) . "Method');\n";
    }
}

$routesPhp .= "\nreturn \$router;";

// Write the PHP code into the routes.php file
file_put_contents('routes.php', $routesPhp);

echo "Routes file generated successfully.\n";
