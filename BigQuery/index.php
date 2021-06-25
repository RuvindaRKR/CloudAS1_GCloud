<?php

//#Reference : [2]"app.yaml Configuration File", Google Cloud, 2021. [Online]. Available: https://cloud.google.com/appengine/docs/standard/php7/config/appref. [Accessed: 09- Apr- 2021].

    switch (@parse_url($_SERVER['REQUEST_URI'])['path']) {
        case '/':
            require 'main.php';
            break; 
        case '/task2a.php':
            require 'task2a.php';
            break;  
        case '/task2b.php':
            require 'task2b.php';
            break;  
        case '/task2c.php':
            require 'task2c.php';
            break; 
        default:
            http_response_code(404);
            exit('Not Found');
    }
?>

