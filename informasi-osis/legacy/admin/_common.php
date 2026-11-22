<?php
require_once dirname(__DIR__) . '/includes/functions.php';
osis_require_role('admin');
function osis_admin_page_start($title)
{
    $GLOBALS['pageTitle'] = $title;
    $pageTitle = $title;
    require dirname(__DIR__) . '/includes/admin_header.php';
}
function osis_admin_page_end()
{
    require dirname(__DIR__) . '/includes/admin_footer.php';
}
function osis_admin_is_post()
{
    return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
}
function osis_admin_handle_error($exception)
{
    if ($exception instanceof RuntimeException) return $exception->getMessage();
    return 'Perubahan tidak dapat disimpan. Periksa kembali data dan pastikan tidak ada data duplikat.';
}
