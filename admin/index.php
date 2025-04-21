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
            // Before deleting, remove from days.json if published
            $filename = basename($fileToDelete);
            $date = str_replace('-', '/', str_replace('.csv', '', $filename));
            $daysJsonPath = __DIR__ . '/../days.json';

            if (file_exists($daysJsonPath)) {
                $daysData = json_decode(file_get_contents($daysJsonPath), true);
                $newDays = [];
                $removed = false;

                foreach ($daysData['days'] as $day) {
                    if ($day['date'] !== $date) {
                        $newDays[] = $day;
                    } else {
                        $removed = true;
                    }
                }

                if ($removed) {
                    $daysData['days'] = $newDays;
                    updateJsonFile($daysJsonPath, $daysData);
                }
            }

            // Now delete the file
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

// Function to update the JSON file with file locking and comment preservation
function updateJsonFile($daysJsonPath, $daysData) {
    $fp = fopen($daysJsonPath, 'w');
    if (!$fp) {
        return false;
    }
    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        return false;
    }
    $jsonContent = json_encode($daysData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $fileContent = "// filepath: " . $daysJsonPath . "\n" . $jsonContent;
    $result = fwrite($fp, $fileContent);
    flock($fp, LOCK_UN);
    fclose($fp);
    return ($result !== false);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng Quản Trị</title>
    <link rel="stylesheet" href="common-style.css">
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
                <?php
                // Load days.json to check public statuses
                $daysJsonPath = __DIR__ . '/../days.json';
                $daysData = [];
                $publicFiles = [];
                if (file_exists($daysJsonPath)) {
                    $daysData = json_decode(file_get_contents($daysJsonPath), true);
                    if (isset($daysData['days']) && is_array($daysData['days'])) {
                        foreach ($daysData['days'] as $day) {
                            $publicFiles[] = str_replace('/', '-', $day['date']) . '.csv';
                        }
                    }
                }
                
                foreach ($csvFiles as $file):
                    $filename = basename($file);
                    $isPublic = in_array($filename, $publicFiles);
                    $statusClass = $isPublic ? 'public' : 'not-public';
                ?>
                    <li class="file-item <?= $selectedFile === $file ? 'selected' : '' ?> <?= $statusClass ?>">
                        <div class="file-header">
                            <a href="?file=<?= urlencode($file) ?>"><?= $filename ?></a>
                            <div class="file-actions">
                                <form method="POST" class="inline-form public-form">
                                    <input type="hidden" name="toggle_public" value="<?= htmlspecialchars($file) ?>">
                                    <input type="hidden" name="current_status" value="<?= $isPublic ? '1' : '0' ?>">
                                    <button type="button" class="public-button <?= $isPublic ? 'is-public' : '' ?>" 
                                            onclick="togglePublic(this, '<?= $filename ?>', <?= $isPublic ? 'true' : 'false' ?>)">
                                        <?= $isPublic ? 'Unpublic' : 'Public' ?>
                                    </button>
                                </form>
                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="delete_file" value="<?= htmlspecialchars($file) ?>">
                                    <button type="submit" class="delete-button" onclick="confirmDelete(event, '<?= $filename ?>')">Xóa</button>
                                </form>
                            </div>
                        </div>
                        <?php if ($selectedFile === $file): ?>
                            <div class="edit-form-container">
                                <form method="POST" id="edit-form">
                                    <input type="hidden" name="file" value="<?= htmlspecialchars($selectedFile) ?>">
                                    <textarea name="content" rows="15"><?= htmlspecialchars($fileContent) ?></textarea>
                                    <div class="form-buttons">
                                        <button type="submit" class="save-button">Lưu Thay Đổi</button>
                                        <button type="button" class="close-button" onclick="hideEditForm()">Đóng</button>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

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