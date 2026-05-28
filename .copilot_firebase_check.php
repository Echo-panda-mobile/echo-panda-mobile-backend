<?php
require 'vendor/autoload.php';
$path = '/var/www/html/firebase-service-account.json';
$exists = file_exists($path);
var_dump($path, $exists);
$factory = (new Kreait\Firebase\Factory())->withServiceAccount($path)->withProjectId('echo-panda-auth');
$auth = $factory->createAuth();
var_dump(get_class($auth));
try {
    $user = $auth->getUserByEmail('test@example.com');
    var_dump('user-ok', $user->uid);
} catch (Throwable $e) {
    var_dump('error', $e->getMessage());
}
