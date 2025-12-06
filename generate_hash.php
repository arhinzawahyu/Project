<?php
$password = 'admin123';
echo "Hash untuk password '$password':\n";
echo password_hash($password, PASSWORD_BCRYPT);
?>