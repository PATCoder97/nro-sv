<?php
require_once 'cvhvn/autoload.php';

// Kiểm tra các item có gender = 3
$query = $CVH->query("SELECT id, NAME, gender FROM item_template WHERE gender = 3 LIMIT 10");
echo "<h3>Items có gender = 3:</h3>";
if (mysqli_num_rows($query) > 0) {
    while ($row = mysqli_fetch_assoc($query)) {
        echo "ID: {$row['id']} - Name: {$row['NAME']} - Gender: {$row['gender']}<br>";
    }
} else {
    echo "Không có item nào có gender = 3<br>";
}

// Kiểm tra các item có gender = NULL
$query = $CVH->query("SELECT id, NAME, gender FROM item_template WHERE gender IS NULL LIMIT 10");
echo "<h3>Items có gender = NULL:</h3>";
if (mysqli_num_rows($query) > 0) {
    while ($row = mysqli_fetch_assoc($query)) {
        echo "ID: {$row['id']} - Name: {$row['NAME']} - Gender: " . ($row['gender'] === null ? 'NULL' : $row['gender']) . "<br>";
    }
} else {
    echo "Không có item nào có gender = NULL<br>";
}

// Kiểm tra các giá trị gender khác nhau
$query = $CVH->query("SELECT DISTINCT gender, COUNT(*) as count FROM item_template GROUP BY gender ORDER BY gender");
echo "<h3>Phân bố gender:</h3>";
if (mysqli_num_rows($query) > 0) {
    while ($row = mysqli_fetch_assoc($query)) {
        $gender = $row['gender'] === null ? 'NULL' : $row['gender'];
        echo "Gender: {$gender} - Count: {$row['count']}<br>";
    }
}
?>
