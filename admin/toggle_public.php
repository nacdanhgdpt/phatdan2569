<?php
session_start();

if (empty($_SESSION['logged_in'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$response = ['success' => false, 'message' => 'Unknown error'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filename = $_POST['filename'] ?? '';
    $action = $_POST['action'] ?? '';
    
    if ($filename && ($action === 'publish' || $action === 'unpublish')) {
        $daysJsonPath = __DIR__ . '/../days.json';
        
        if (file_exists($daysJsonPath)) {
            $daysData = json_decode(file_get_contents($daysJsonPath), true);
            
            if ($action === 'publish') {
                // Extract date part and add to days.json
                $dateParts = explode('-', str_replace('.csv', '', $filename));
                if (count($dateParts) >= 2) {
                    $date = $dateParts[0] . '/' . $dateParts[1];
                    
                    // Determine the day of the week for the date
                    $dayName = determineDayName($date); // You need to implement this function
                    
                    // Check if already exists
                    $exists = false;
                    foreach ($daysData['days'] as $day) {
                        if ($day['date'] === $date) {
                            $exists = true;
                            break;
                        }
                    }
                    
                    if (!$exists) {
                        $daysData['days'][] = [
                            'date' => $date,
                            'day' => $dayName,
                            'display' => $date . ' (' . $dayName . ')'
                        ];
                        if (updateJsonFile($daysJsonPath, $daysData)) {
                            $response = ['success' => true, 'message' => 'File published successfully'];
                        } else {
                            $response = ['success' => false, 'message' => 'Failed to update days.json'];
                        }
                    } else {
                        $response = ['success' => false, 'message' => 'File is already published'];
                    }
                } else {
                    $response = ['success' => false, 'message' => 'Invalid filename format'];
                }
            } else {
                // Remove from days.json
                $date = str_replace('-', '/', str_replace('.csv', '', $filename));
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
                    if (updateJsonFile($daysJsonPath, $daysData)) {
                        $response = ['success' => true, 'message' => 'File unpublished successfully'];
                    } else {
                        $response = ['success' => false, 'message' => 'Failed to update days.json'];
                    }
                } else {
                    $response = ['success' => false, 'message' => 'File was not published'];
                }
            }
        } else {
            $response = ['success' => false, 'message' => 'days.json file not found'];
        }
    } else {
        $response = ['success' => false, 'message' => 'Missing required parameters'];
    }
}

header('Content-Type: application/json');
echo json_encode($response);

// Helper function to determine the day name with improved accuracy
function determineDayName($date) {
    $dateParts = explode('/', $date);
    if (count($dateParts) >= 2) {
        $day = intval($dateParts[0]);
        $month = intval($dateParts[1]);
        $year = date('Y'); // Current year
        
        // Make sure we have valid date components
        if ($day < 1 || $day > 31 || $month < 1 || $month > 12) {
            return 'Unknown';
        }
        
        // Format with leading zeros to ensure proper parsing
        $formattedDate = sprintf("%02d-%02d-%04d", $day, $month, $year);
        
        // Use DateTime for more reliable date handling
        try {
            $dateObj = new DateTime($formattedDate);
            $dayOfWeek = $dateObj->format('w'); // 0 (Sunday) through 6 (Saturday)
            
            $dayNames = [
                '0' => 'Chủ Nhật',
                '1' => 'Thứ Hai',
                '2' => 'Thứ Ba',
                '3' => 'Thứ Tư',
                '4' => 'Thứ Năm',
                '5' => 'Thứ Sáu',
                '6' => 'Thứ Bảy'
            ];
            
            return $dayNames[$dayOfWeek];
        } catch (Exception $e) {
            error_log("Date parsing error: " . $e->getMessage());
            return 'Unknown';
        }
    }
    
    return 'Unknown';
}

// Function to update the JSON file with file locking (no comment line!)
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
    $result = fwrite($fp, $jsonContent);
    flock($fp, LOCK_UN);
    fclose($fp);
    return ($result !== false);
}
