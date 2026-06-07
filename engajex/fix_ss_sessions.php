<?php
// fix_ss_sessions.php
$dir = __DIR__ . '/modulosrs/softskill/src';

$replacements = [
    "\$_SESSION['user_id']" => "\$_SESSION['ss_id']",
    "\$_SESSION['role']" => "\$_SESSION['ss_role']",
    "\$_SESSION['user_name']" => "\$_SESSION['ss_user_name']"
];

$it = new RecursiveDirectoryIterator($dir);
foreach (new RecursiveIteratorIterator($it) as $file) {
    if ($file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $newContent = str_replace(array_keys($replacements), array_values($replacements), $content);
        if ($newContent !== $content) {
            file_put_contents($file->getPathname(), $newContent);
            echo "Fixed: " . $file->getPathname() . "\n";
        }
    }
}
?>
