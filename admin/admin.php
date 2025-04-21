<!-- filepath: f:\wampp\www\phatdan2569\admin.php -->
<?php
session_start();

if (empty($_SESSION['logged_in'])) {
    header('Location: ../login.php');
    exit;
}

$csvDir = __DIR__ . '/../schedules'; // Thư mục chứa các file CSV
$csvFiles = glob("$csvDir/*.csv"); // Lấy tất cả các file CSV trong thư mục
$selectedFile = $_GET['file'] ?? null;
$fileContent = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file'])) {
    $selectedFile = $_POST['file'];
    $updatedContent = $_POST['content'] ?? '';

    // Lưu nội dung đã chỉnh sửa vào file được chọn
    file_put_contents($selectedFile, $updatedContent);
    $message = "File '$selectedFile' đã được cập nhật thành công.";
}

// Tải nội dung của file được chọn
if ($selectedFile && file_exists($selectedFile)) {
    $fileContent = file_get_contents($selectedFile);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng Quản Trị</title>
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <div class="container">
        <h1>Bảng Quản Trị</h1>
        <p>Chào mừng đến với bảng quản trị! Sử dụng giao diện này để quản lý các file CSV.</p>

        <h2>Danh Sách File CSV</h2>
        <ul>
            <?php foreach ($csvFiles as $file): ?>
                <li>
                    <a href="?file=<?= urlencode($file) ?>"><?= basename($file) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if ($selectedFile): ?>
            <h2>Chỉnh Sửa: <?= basename($selectedFile) ?></h2>
            <?php if ($message): ?>
                <p class="message"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="file" value="<?= htmlspecialchars($selectedFile) ?>">
                <textarea name="content" rows="20"><?= htmlspecialchars($fileContent) ?></textarea>
                <button type="submit">Lưu Thay Đổi</button>
            </form>
        <?php endif; ?>

        <a href="logout.php" class="logout">Đăng xuất</a>
    </div>
</body>
</html>