<?php
require_once dirname(__DIR__) . '/includes/functions.php';
osis_require_role('anggota');
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
osis_redirect('tugas/detail.php?id=' . $id);
