<?php
spl_autoload_register(function ($class_name) {
    $split = explode('\\', $class_name);
    include 'classes/' .  implode('/', $split) . '.php';
});
