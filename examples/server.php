<?php
echo json_encode([
    'server' => $_SERVER,
    'cookie' => $_COOKIE,
    'get'    => $_GET,
    'post'   => $_POST,
    'files'  => $_FILES,
    'input'  => file_get_contents('php://input'),
]);
