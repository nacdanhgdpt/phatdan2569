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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['file'])) {
        $selectedFile = $_POST['file'];
        $updatedContent = $_POST['content'] ?? '';

        // Lưu nội dung đã chỉnh sửa vào file được chọn
        file_put_contents($selectedFile, $updatedContent);
        $message = "File '$selectedFile' đã được cập nhật thành công.";
    } elseif (isset($_POST['new_date'])) {
        $newDate = $_POST['new_date'];
        $newFileName = $csvDir . '/' . str_replace('/', '-', substr($newDate, 0, 5)) . '.csv'; // Remove the year

        // Tạo file CSV mới với dữ liệu mẫu
        $sampleData = "time,title,leader,team,tools\n" .
                      "8:00 - 10:00,Sample Task,Leader Name,Team Member 1,Team Member 2,Sample Tools\n";
        file_put_contents($newFileName, $sampleData);
        $message = "File lịch mới '" . basename($newFileName) . "' đã được tạo thành công.";

        // Refresh the list of CSV files
        $csvFiles = glob("$csvDir/*.csv");
    } elseif (isset($_POST['delete_file'])) {
        $fileToDelete = $_POST['delete_file'];
        if (file_exists($fileToDelete)) {
            unlink($fileToDelete);
            $message = "File '" . basename($fileToDelete) . "' đã được xóa thành công.";
            $csvFiles = glob("$csvDir/*.csv"); // Refresh the list
        } else {
            $message = "File không tồn tại.";
        }
    }
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
    <link rel="stylesheet" href="../common-style.css">
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <div class="container">
        <h1>Bảng Quản Trị</h1>
        <p>Chào mừng đến với bảng quản trị! Sử dụng giao diện này để quản lý các file CSV.</p>

        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <div class="section">
            <h2>Danh Sách File CSV</h2>
            <ul class="csv-list">
                <?php foreach ($csvFiles as $file): ?>
                    <li>
                        <a href="?file=<?= urlencode($file) ?>"><?= basename($file) ?></a>
                        <form method="POST" class="inline-form">
                            <input type="hidden" name="delete_file" value="<?= htmlspecialchars($file) ?>">
                            <button type="submit" class="delete-button" onclick="confirmDelete(event, '<?= basename($file) ?>')">Xóa</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <?php if ($selectedFile): ?>
            <div class="section">
                <h2>Chỉnh Sửa: <?= basename($selectedFile) ?></h2>
                <form method="POST" id="edit-form">
                    <input type="hidden" name="file" value="<?= htmlspecialchars($selectedFile) ?>">
                    <textarea name="content" rows="20"><?= htmlspecialchars($fileContent) ?></textarea>
                    <div class="form-buttons">
                        <button type="submit" class="save-button">Lưu Thay Đổi</button>
                        <button type="button" class="close-button" onclick="hideEditForm()">Đóng</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="section">
            <h2>Tạo Lịch Mới</h2>
            <form method="POST" id="create-schedule-form">
                <label for="new_date">Chọn Ngày (dd/mm/yyyy):</label>
                <input type="text" id="new_date" name="new_date" placeholder="dd/mm/yyyy" required>
                <button type="submit" class="create-button">Tạo Lịch</button>
            </form>
        </div>

        <a href="logout.php" class="logout">Đăng xuất</a>
    </div>
    <script src="admin.js"></script>
</body>
</html>