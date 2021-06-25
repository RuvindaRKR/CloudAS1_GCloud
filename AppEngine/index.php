<?php

//#Reference : [2]"app.yaml Configuration File", Google Cloud, 2021. [Online]. Available: https://cloud.google.com/appengine/docs/standard/php7/config/appref. [Accessed: 09- Apr- 2021].

    switch (@parse_url($_SERVER['REQUEST_URI'])['path']) {
        case '/':
            require 'login.php';
            break;
        case '/main.php':
            require 'main.php';
            break;
        case '/register.php':
            require 'register.php';
            break;    
        case '/logout.php':
            require 'logout.php';
            break;     
        case '/user.php':
            require 'user.php';
            break;     
        case '/edit.php':
            require 'edit.php';
            break;     
        default:
            http_response_code(404);
            exit('Not Found');
    }
?>

