<?php
$pageTitle = 'Nhật ký Chatbot';
require_once '../config/config.php';

if (!isAdmin() && !isStaff()) {
    redirect('../index.php');
}

require_once '../includes/header.php';

$db = getDB();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

$total = (int)$db->query('SELECT COUNT(*) FROM chat_logs')->fetchColumn();
$logs = $db->prepare('SELECT cl.*, u.full_name as user_full_name FROM chat_logs cl LEFT JOIN users u ON cl.user_id = u.id ORDER BY cl.created_at DESC LIMIT ? OFFSET ?');
$logs->bindValue(1, $perPage, PDO::PARAM_INT);
$logs->bindValue(2, $offset, PDO::PARAM_INT);
$logs->execute();
$logs = $logs->fetchAll();

$totalPages = (int)ceil($total / $perPage);
?>

<div class="admin-container">
    <div class="admin-header">
        <h1><i class="fas fa-comments"></i> Nhật ký Chatbot</h1>
    </div>

    <div class="admin-section">
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Người dùng</th>
                        <th>Message</th>
                        <th>Reply</th>
                        <th>Source</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo e($log['id']); ?></td>
                        <td><?php echo e($log['user_full_name'] ?? $log['username'] ?? 'Khách'); ?></td>
                        <td style="max-width:300px;white-space:pre-wrap;"><?php echo e($log['message']); ?></td>
                        <td style="max-width:300px;white-space:pre-wrap;"><?php echo e($log['reply']); ?></td>
                        <td><?php echo e($log['source']); ?></td>
                        <td><?php echo e($log['created_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6">Chưa có bản ghi nào</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <a href="chat_logs.php?page=<?php echo $p; ?>" class="<?php echo $p === $page ? 'active' : ''; ?>"><?php echo $p; ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
