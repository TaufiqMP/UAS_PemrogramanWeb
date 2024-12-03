<?php
session_start();
include('config.php');

// Check if the user is logged in
if (!isset($_SESSION['id_user'])) {
    header("Location: index.php?message=Please log in to make a reservation.");
    exit();
}

$id_user = $_SESSION['id_user'];

// Function to check available tables
function checkAvailableTable($pax, $conn) {
    $stmt = $conn->prepare("SELECT id_meja FROM meja WHERE slot_kursi >= ? AND jumlah_tersedia > 0 ORDER BY slot_kursi ASC LIMIT 1");
    $stmt->bind_param("i", $pax);
    $stmt->execute();
    $stmt->bind_result($id_meja);
    $stmt->fetch();
    $stmt->close();
    return $id_meja;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['Continue'])) {
    $pax = $conn->real_escape_string($_POST['pax']);
    $date = $conn->real_escape_string($_POST['date']);
    $time = $conn->real_escape_string($_POST['time']);
    $note = $conn->real_escape_string($_POST['note']);
    $qr_code = bin2hex(random_bytes(16));
    $status = 'aktif';

    // Mark any active reservation as inactive
    $stmt = $conn->prepare("UPDATE reservasi SET status = 'inactive' WHERE id_user = ? AND status = 'aktif'");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();

    $id_meja = checkAvailableTable($pax, $conn);
    if ($id_meja === null) {
        echo "<script>alert('No available table for the requested number of pax.');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO reservasi (id_user, id_meja, tanggal_reservasi, waktu_reservasi, status, qr_code) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissss", $id_user, $id_meja, $date, $time, $status, $qr_code);
        if ($stmt->execute()) {
            $_SESSION['reservasi_id'] = $stmt->insert_id;
            header("Location: order.php?qr_code=$qr_code");
            exit();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwetiau Djuara - Reservasi</title>
    <link rel="icon" type="image" href="https://i.imgur.com/uTgr4G3.jpeg">
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inria+Serif:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="home.php">
                    <img src="https://i.imgur.com/uTgr4G3.jpeg" alt="Logo" class="logo-img">
                </a>
                <a href="home.php" class="brand"></a>
            </div>
            <ul class="nav-links">
                <li><a href="home.php">Home</a></li>
                <li><a href="index.php" class="active">Reservasi</a></li>
                <li><a href="my-reservasi.php">My Reservasi</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="contact.php">Kontak</a></li>
                <li><a href="about.php">About</a></li>
            </ul>
        </nav>
    </header>
    <section class="res" id="home">
        <div class="res-c">
            <h1>Reservasi</h1>
        </div>
    </section>
    <section class="rus">
        <div>
            <h3>Reservasi</h3><br>
            <p class="stupid"><b>Schedule - Menu - Summary</b></p>
        </div>
        <div class="rus-c">
            <form method="post" action="reservasi.php">
                <label for="pax">Select Number of Pax</label><br />
                <input type="text" name="pax" id="pax" placeholder="Pax" required /><br /><br />
                <label for="date">Select Reservation Date</label><br />
                <input type="date" name="date" id="date" required /><br /><br />
                <label for="time">Select Reservation Time</label><br />
                <input type="time" name="time" id="time" list="times" required /><br /><br />
                <datalist id="times">
                    <option value="01:00:00">
                    <option value="02:00:00">
                    <option value="03:00:00">
                    <option value="04:00:00">
                    <option value="05:00:00">
                    <option value="06:00:00">
                    <option value="07:00:00">
                    <option value="08:00:00">
                    <option value="09:00:00">
                    <option value="10:00:00">
                    <option value="11:00:00">
                    <option value="12:00:00">
                    <option value="13:00:00">
                    <option value="14:00:00">
                    <option value="15:00:00">
                    <option value="16:00:00">
                    <option value="17:00:00">
                    <option value="18:00:00">
                    <option value="19:00:00">
                    <option value="20:00:00">
                    <option value="21:00:00">
                    <option value="22:00:00">
                    <option value="23:00:00">
                    <option value="00:00:00">
                </datalist>
                <label for="note">Note (Optional)</label><br />
                <input type="text" name="note" id="note" placeholder="Note" /><br /><br />
                <input type="submit" name="Continue" value="Continue"
                    style="background-color: #ff7f50; font-weight: bold;" />
            </form>
        </div>
    </section>
</body>
</html>
