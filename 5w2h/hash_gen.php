<?php
// Generates a hash for 'Kyew@1802' or any password you want
$pass = 'Kyew@1802';
echo "Senha: $pass <br>";
echo "Hash: " . password_hash($pass, PASSWORD_DEFAULT);
?>