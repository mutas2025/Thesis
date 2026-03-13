<?php
// Run this file once to generate the hash
 $password = "1234"; 
echo password_hash($password, PASSWORD_BCRYPT);
?>