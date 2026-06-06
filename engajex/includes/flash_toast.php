<?php
/**
 * flash_toast.php — PHP helper to emit a toast notification from server-side logic.
 *
 * Usage (anywhere after sidebar.php has been included so toast.js is loaded):
 *   flashToast('Feedback enviado com sucesso!', 'success');
 *   flashToast('Erro ao salvar.', 'error');
 *
 * The function outputs a <script> block that fires Toast.show() once the DOM
 * is ready, so it can be called anywhere in the page body.
 */
if (!function_exists('flashToast')) {
    function flashToast(string $message, string $type = 'info'): void {
        $allowed = ['success', 'error', 'warning', 'info'];
        if (!in_array($type, $allowed, true)) $type = 'info';
        $msg = htmlspecialchars($message, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        echo "<script>document.addEventListener('DOMContentLoaded',function(){"
           . "if(window.Toast)Toast." . $type . "('" . addslashes($message) . "');"
           . "});</script>\n";
    }
}
