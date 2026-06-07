<?php
session_start();
echo "<h1>Diagnóstico de Sessão</h1>";
echo "<p>Caminho atual: " . $_SERVER['PHP_SELF'] . "</p>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
if (isset($_SESSION['user_id'])) {
    echo "<p style='color:green'>✅ Sessão do Engajex detectada!</p>";
} else {
    echo "<p style='color:red'>❌ Sessão do Engajex NÃO detectada.</p>";
    echo "<p>Isso significa que o cookie de login não chegou nesta pasta.</p>";
}
?>
