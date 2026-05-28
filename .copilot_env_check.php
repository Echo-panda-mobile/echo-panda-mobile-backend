<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
var_dump(array_key_exists('FIREBASE_CREDENTIALS', $_ENV) ? $_ENV['FIREBASE_CREDENTIALS'] : 'missing');
var_dump(array_key_exists('FIREBASE_CREDENTIALS', $_SERVER) ? $_SERVER['FIREBASE_CREDENTIALS'] : 'missing');
var_dump(getenv('FIREBASE_CREDENTIALS'));
var_dump(array_key_exists('FIREBASE_PROJECT_ID', $_ENV) ? $_ENV['FIREBASE_PROJECT_ID'] : 'missing');
var_dump(getenv('FIREBASE_PROJECT_ID'));
