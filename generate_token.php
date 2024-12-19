<?php
include('library/crud.php');
include('library/functions.php');
include_once('library/verify-token.php');
$token = generate_token();
print_r($token);
