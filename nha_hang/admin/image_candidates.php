<?php
$pageTitle = 'Ảnh gợi ý cho thực đơn';
require_once '../config/config.php';

if (!isAdmin() && !isStaff()) {
    redirect('../index.php');
}

require_once '../includes/header.php';

$candidatesFile = __DIR__ . '/../tools/image_candidates.json';
$data = [];
if (file_exists($candidatesFile)) {
    $data = json_decode(file_get_contents($candidatesFile), true) ?: [];
}

?>
<div class="admin-container">
    <h1><i class="fas fa-image"></i> Ảnh gợi ý cho thực đơn</h1>
    <p>Chọn ảnh phù hợp (chỉ hiển thị ảnh từ Wikimedia Commons/Upload) và nhấn Cập nhật để tải về và cập nhật CSDL.</p>

    <form method="POST" action="apply_images.php">
        <div class="admin-section">
            <?php if (empty($data)): ?>
                <div class="alert alert-info">Không có dữ liệu. Hãy chạy <code>php tools/find_free_images.php</code> để tạo danh sách.</div>
            <?php else: ?>
                <?php foreach ($data as $item): ?>
                    <div class="image-candidate-card">
                        <h3><?php echo e($item['name']); ?> <small>(ID: <?php echo $item['id']; ?>)</small></h3>
                        <?php if (empty($item['candidates'])): ?>
                            <p>Không tìm thấy ảnh phù hợp.</p>
                        <?php else: ?>
                            <div class="candidates-grid">
                                <?php foreach ($item['candidates'] as $idx => $c): ?>
                                    <label class="candidate">
                                        <input type="radio" name="choice[<?php echo $item['id']; ?>]" value="<?php echo htmlspecialchars($c['image_url']); ?>">
                                        <div class="thumb">
                                            <img src="<?php echo e($c['image_url']); ?>" alt="">
                                        </div>
                                        <div class="meta">
                                            <div class="title"><?php echo e($c['title'] ?? ''); ?></div>
                                            <div class="license">Giấy phép: <?php echo e($c['license'] ?? ''); ?></div>
                                            <div class="artist">Tác giả: <?php echo e($c['artist'] ?? $c['credit'] ?? ''); ?></div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (!empty($data)): ?>
            <div style="margin-top:18px">
                <button type="submit" class="btn btn-primary">Cập nhật ảnh đã chọn</button>
            </div>
        <?php endif; ?>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
