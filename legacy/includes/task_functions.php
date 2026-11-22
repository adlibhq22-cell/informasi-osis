<?php
require_once __DIR__ . '/functions.php';

function osis_task_assignee_options()
{
    global $pdo;
    return $pdo->query("SELECT a.id AS anggota_id,a.nama,a.kelas,u.role,u.username FROM anggota a JOIN users u ON u.id=a.user_id WHERE a.status='aktif' AND u.aktif=1 AND u.role IN ('anggota','pengurus') ORDER BY CASE WHEN u.role='pengurus' THEN 0 ELSE 1 END,a.kelas,a.nama")->fetchAll();
}

function osis_task_get($taskId)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT t.*,u.nama AS pembuat_nama,done.nama AS penyelesai_nama FROM tugas t JOIN users u ON u.id=t.dibuat_oleh LEFT JOIN users done ON done.id=t.diselesaikan_oleh WHERE t.id=? LIMIT 1');
    $stmt->execute([(int) $taskId]);
    return $stmt->fetch();
}

function osis_task_can_manage($task)
{
    $user = osis_current_user();
    return $user && ($user['role'] === 'admin' || ($user['role'] === 'pengurus' && (int) $task['dibuat_oleh'] === (int) $user['id']));
}

function osis_task_my_assignment($taskId)
{
    global $pdo;
    $memberId = osis_current_member_id();
    if (!$memberId) return null;
    $stmt = $pdo->prepare('SELECT * FROM tugas_penerima WHERE tugas_id=? AND anggota_id=? LIMIT 1');
    $stmt->execute([(int) $taskId, $memberId]);
    return $stmt->fetch();
}

function osis_task_all_assignments_submitted($taskId)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total,COALESCE(SUM(status='terkirim'),0) AS terkirim FROM tugas_penerima WHERE tugas_id=?");
    $stmt->execute([(int) $taskId]);
    $row = $stmt->fetch();
    return (int) $row['total'] > 0 && (int) $row['total'] === (int) $row['terkirim'];
}

function osis_task_progress($taskId)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total,COALESCE(SUM(status='terkirim'),0) AS terkirim FROM tugas_penerima WHERE tugas_id=?");
    $stmt->execute([(int) $taskId]);
    $row = $stmt->fetch();
    $total = (int) $row['total'];
    $submitted = (int) $row['terkirim'];
    return ['total' => $total, 'terkirim' => $submitted, 'percent' => $total ? (int) round(($submitted / $total) * 100) : 0];
}

function osis_task_record_event($taskId, $userId, $action, $description)
{
    global $pdo;
    $allowed = ['dibuat','pengumpulan','selesai'];
    if (!in_array($action, $allowed, true)) throw new InvalidArgumentException('Jenis riwayat tugas tidak valid.');
    $stmt = $pdo->prepare('INSERT INTO tugas_riwayat(tugas_id,user_id,aksi,keterangan) VALUES(?,?,?,?)');
    $stmt->execute([(int) $taskId, $userId ? (int) $userId : null, $action, mb_substr((string) $description, 0, 255, 'UTF-8')]);
}

function osis_task_get_events($taskId)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT h.*,u.nama AS pelaku FROM tugas_riwayat h LEFT JOIN users u ON u.id=h.user_id WHERE h.tugas_id=? ORDER BY h.created_at DESC,h.id DESC');
    $stmt->execute([(int) $taskId]);
    return $stmt->fetchAll();
}

function osis_task_priority_label($priority)
{
    $labels = ['rendah' => 'Rendah', 'normal' => 'Normal', 'tinggi' => 'Prioritas tinggi'];
    return isset($labels[$priority]) ? $labels[$priority] : 'Normal';
}

function osis_task_page_start($title)
{
    if (osis_current_user() && osis_current_user()['role'] === 'admin') {
        require_once dirname(__DIR__) . '/admin/_common.php';
        osis_admin_page_start($title);
    } else {
        $GLOBALS['pageTitle'] = $title;
        $pageTitle = $title;
        require dirname(__DIR__) . '/includes/header.php';
    }
}

function osis_task_page_end()
{
    if (osis_current_user() && osis_current_user()['role'] === 'admin') {
        osis_admin_page_end();
    } else {
        require dirname(__DIR__) . '/includes/footer.php';
    }
}

function osis_task_detail_url($taskId)
{
    return osis_url('tugas/detail.php?id=' . (int) $taskId);
}

function osis_task_member_task_query()
{
    return "SELECT t.*,tp.id AS penugasan_id,tp.status AS status_penerima,u.nama AS pembuat_nama,(SELECT COUNT(*) FROM tugas_penerima x WHERE x.tugas_id=t.id) AS penerima_total,(SELECT COALESCE(SUM(x.status='terkirim'),0) FROM tugas_penerima x WHERE x.tugas_id=t.id) AS penerima_terkirim FROM tugas_penerima tp JOIN tugas t ON t.id=tp.tugas_id JOIN users u ON u.id=t.dibuat_oleh";
}

function osis_task_card_html($task, $showAssignment = false)
{
    $progressTotal = isset($task['penerima_total']) ? (int) $task['penerima_total'] : 0;
    $progressSent = isset($task['penerima_terkirim']) ? (int) $task['penerima_terkirim'] : 0;
    $progressPercent = $progressTotal ? (int) round(($progressSent / $progressTotal) * 100) : 0;
    $cardStatus = $task['status'] === 'selesai' ? 'selesai' : (isset($task['status_penerima']) ? $task['status_penerima'] : $task['status']);
    $statusText = $task['status'] === 'selesai' ? 'Tugas selesai' : (isset($task['status_penerima']) ? ($task['status_penerima'] === 'terkirim' ? 'Kiriman terkirim' : 'Menunggu kiriman') : 'Aktif');
    $deadlineText = $task['deadline'] ? osis_format_date($task['deadline']) : 'Tanpa tenggat';
    $overdue = $task['deadline'] && $task['status'] === 'aktif' && strtotime($task['deadline']) < strtotime(date('Y-m-d'));
    $html = '<a class="task-card" href="' . osis_e(osis_task_detail_url($task['id'])) . '">';
    $html .= '<div class="task-card-top"><span class="task-state-chip state-' . osis_e($cardStatus) . '">' . osis_e($statusText) . '</span><span class="task-priority-chip priority-' . osis_e($task['prioritas']) . '">' . osis_e(osis_task_priority_label($task['prioritas'])) . '</span></div>';
    $html .= '<h3>' . osis_e($task['judul']) . '</h3><p>' . osis_e(mb_strimwidth(strip_tags((string) $task['deskripsi']), 0, 150, '…', 'UTF-8')) . '</p>';
    $html .= '<div class="task-card-meta"><span>◷ ' . osis_e($deadlineText) . ($overdue ? ' · Terlambat' : '') . '</span>';
    if (!empty($task['pembuat_nama'])) $html .= '<span>Dari ' . osis_e($task['pembuat_nama']) . '</span>';
    $html .= '</div>';
    $html .= '<div class="task-card-progress"><div class="task-progress-track"><span style="width:' . $progressPercent . '%"></span></div><small>' . $progressSent . '/' . $progressTotal . ' kiriman masuk</small></div>';
    if ($showAssignment && isset($task['status_penerima'])) $html .= '<span class="task-card-footer">Buka tugas <b>↗</b></span>';
    else $html .= '<span class="task-card-footer">Detail tugas <b>↗</b></span>';
    return $html . '</a>';
}
