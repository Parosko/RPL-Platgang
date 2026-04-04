<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../config/config.php';

checkLogin();

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$mitra_filter = isset($_GET['mitra']) ? $_GET['mitra'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'created_at_desc';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$base_query = "SELECT p.*, 
                      (SELECT COUNT(*) FROM lamaran l WHERE l.peluang_id = p.id) as applicant_count,
                      m.nama_organisasi as nama_mitra
               FROM peluang p 
               LEFT JOIN mitra m ON p.mitra_id = m.user_id
               WHERE p.status = 'approved'";

$params = [];
$types = "";

if (!empty($search_query)) {
    $base_query .= " AND (p.judul LIKE ? OR p.deskripsi LIKE ?)";
    $search_param = "%" . $search_query . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($status_filter)) {
    if ($status_filter === 'open') {
        $base_query .= " AND p.closed_at IS NULL AND p.deadline > NOW()";
    } elseif ($status_filter === 'closed') {
        $base_query .= " AND (p.closed_at IS NOT NULL OR p.deadline <= NOW())";
    }
}

if (!empty($mitra_filter)) {
    $base_query .= " AND m.nama_organisasi LIKE ?";
    $params[] = "%" . $mitra_filter . "%";
    $types .= "s";
}

switch ($sort_by) {
    case 'created_at_asc':
        $base_query .= " ORDER BY p.created_at ASC";
        break;
    case 'deadline_asc':
        $base_query .= " ORDER BY p.deadline ASC";
        break;
    case 'deadline_desc':
        $base_query .= " ORDER BY p.deadline DESC";
        break;
    case 'applicant_count_desc':
        $base_query .= " ORDER BY applicant_count DESC";
        break;
    case 'created_at_desc':
    default:
        $base_query .= " ORDER BY p.created_at DESC";
        break;
}

$count_query = str_replace("SELECT p.*, (SELECT COUNT(*) FROM lamaran l WHERE l.peluang_id = p.id) as applicant_count, m.nama_organisasi as nama_mitra", 
                           "SELECT COUNT(*) as total", $base_query);

if (!empty($params)) {
    $stmt_count = $conn->prepare($count_query);
    $stmt_count->bind_param($types, ...$params);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $total_posts = $result_count->fetch_assoc()['total'];
    $stmt_count->close();
} else {
    $result_count = $conn->query($count_query);
    $total_posts = $result_count->fetch_assoc()['total'];
}

$total_pages = ceil($total_posts / $limit);

$paginated_query = $base_query . " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

if (!empty($params)) {
    $stmt = $conn->prepare($paginated_query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $result = $conn->query($paginated_query);
}

$posts = [];
while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}

$mitra_query = "SELECT DISTINCT m.nama_organisasi 
                FROM mitra m 
                INNER JOIN peluang p ON m.user_id = p.mitra_id 
                WHERE p.status = 'approved'
                ORDER BY m.nama_organisasi";
$mitra_result = $conn->query($mitra_query);
$available_mitras = [];
while ($row = $mitra_result->fetch_assoc()) {
    $available_mitras[] = $row['nama_organisasi'];
}

$conn->close();

$_SESSION['search_results'] = [
    'posts' => $posts,
    'total_posts' => $total_posts,
    'total_pages' => $total_pages,
    'current_page' => $page,
    'search_query' => $search_query,
    'status_filter' => $status_filter,
    'mitra_filter' => $mitra_filter,
    'sort_by' => $sort_by,
    'available_mitras' => $available_mitras
];

header('Location: ' . BASE_URL . '/views/search.php');
exit();
?>
