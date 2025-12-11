<?php
require_once 'inc/auth.php';
logout();
header('Location: login.php');
exit;
