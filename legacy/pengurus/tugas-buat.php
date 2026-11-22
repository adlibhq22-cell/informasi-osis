<?php
require_once dirname(__DIR__) . '/includes/functions.php';
osis_require_role('pengurus');
osis_redirect('tugas/buat.php');
