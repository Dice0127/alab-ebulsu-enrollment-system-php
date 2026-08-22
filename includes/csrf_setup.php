<?php
require_once __DIR__ . '/csrf.php';
$token = csrf_token();
?>
<script>
window.CSRF_TOKEN = "<?php echo htmlspecialchars($token); ?>";
$(document).ajaxSend(function (event, xhr, settings) {
    if (settings.type && settings.type.toUpperCase() === 'POST') {
        xhr.setRequestHeader('X-CSRF-Token', window.CSRF_TOKEN);
    }
});
</script>
