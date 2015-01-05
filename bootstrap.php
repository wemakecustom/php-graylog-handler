<?php

$config = parse_ini_file(__DIR__ . '/config.ini.dist');
if (is_file(__DIR__ . '/config.ini')) {
    $config = array_merge($config, parse_ini_file(__DIR__ . '/config.ini'));
}

if (!is_file(__DIR__ . '/vendor/autoload.php')) {
    return; // not initialized
}

$autoload = require __DIR__ . '/vendor/autoload.php';

switch ($config['transport']) {
    case 'udp':
        $transport = new Gelf\Transport\UdpTransport($config['host'], $config['port']);
        break;

    case 'http':
        $transport = new Gelf\Transport\HttpTransport($config['host'], $config['port'], $config['path']);
        break;

    default: 
        return; // bad config 
}

$publisher = new Gelf\Publisher($transport);
$logger = new Gelf\Logger($publisher);
$getContext = function(array $context = array()) {
    $context['application_name'] = 'php';
    if (getenv('PHP_POOL')) {
        $context['pool'] = getenv('PHP_POOL');
    }
    if (getenv('SERVER_NAME')) {
        $context['http_host'] = getenv('SERVER_NAME');
    } elseif (!empty($_SERVER['SERVER_NAME'])) {
        $context['http_host'] = $_SERVER['SERVER_NAME'];
    }
    if (!empty($_SERVER['REQUEST_URI'])) {
        $context['uri'] = $_SERVER['REQUEST_URI'];
    }

    return $context;
};

foreach (array('error_handler', 'exception_handler', 'fatal_handler') as $handler) {
    if (!empty($config[$handler])) {
        require __DIR__ . "/handlers/${handler}.php";
    }
}

unset($logger, $publisher, $transport, $config, $autoload, $getContext);
