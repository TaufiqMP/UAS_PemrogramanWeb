<?php
session_start();
include('config.php');

$orderDetails = [];
$totalAmount = 0;
$userName = 'Guest';
$reservationDate = '';
$reservationTime = '';
$duration = '90 Minutes';
$reservationFound = false;

if (isset($_SESSION['id_user'])) {
    $id_user = $_SESSION['id_user'];

    $sqlUser = "SELECT nama FROM user WHERE id_user = ?";
    $stmtUser = $conn->prepare($sqlUser);
    $stmtUser->bind_param("i", $id_user);
    $stmtUser->execute();
    $stmtUser->bind_result($userName);
    $stmtUser->fetch();
    $stmtUser->close();

    $sqlReservasi = "SELECT tanggal_reservasi, waktu_reservasi FROM reservasi WHERE id_user = ? ORDER BY id_reservasi DESC LIMIT 1";
    $stmtReservasi = $conn->prepare($sqlReservasi);
    $stmtReservasi->bind_param("i", $id_user);
    $stmtReservasi->execute();
    $stmtReservasi->bind_result($reservationDate, $reservationTime);
    if ($stmtReservasi->fetch()) {
        $reservationFound = true;
    }
    $stmtReservasi->close();
}

if (isset($_SESSION['orderDetails']) && !empty($_SESSION['orderDetails'])) {
    $orderDetails = $_SESSION['orderDetails'];
    $menuIds = array_column($orderDetails, 'id');
    $menuIdsString = implode(',', array_map('intval', $menuIds));

    if (!empty($menuIdsString)) {
        $sql = "SELECT id_menu, nama_menu, deskripsi, harga, foto FROM menu WHERE id_menu IN ($menuIdsString)";
        $result = $conn->query($sql);

        if ($result === false) {
            echo "Error executing query: " . $conn->error;
            exit(); 
        }

        $menuMap = [];
        while ($row = $result->fetch_assoc()) {
            $menuMap[$row['id_menu']] = $row;
            $menuMap[$row['id_menu']]['quantity'] = 0;
        }

        foreach ($orderDetails as $item) {
            if (isset($menuMap[$item['id']])) {
                $menuMap[$item['id']]['quantity'] += $item['quantity'];
                $totalAmount += $menuMap[$item['id']]['harga'] * $item['quantity'];
            }
        }
    } else {
        echo "Error: No valid menu items found in the order.";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservation - Kwetiau Djuara</title>
    <link rel="stylesheet" href="styles.css">
    <script src="script.js"></script>
    <link rel="icon" href="https://i.imgur.com/uTgr4G3.jpeg">
    <link href="https://fonts.googleapis.com/css2?family=Inria+Serif:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <?php echo '<link href="styles.css" rel="stylesheet">'; ?>
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
                <li><a href="menu.php">Menu</a></li>
                <li><a href="contact.php">Kontak</a></li>
                <li><a href="about.php">About</a></li>
            </ul>
            <li class="dropdown user user-menu profilmenu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <img src="https://static-00.iconduck.com/assets.00/profile-major-icon-1024x1024-9rtgyx30.png" class="user-image profil" alt="User Image">
                        <span class="hidden-xs"></span>
                    </a>
                    <ul class="dropdown-menu dropmenu">
                        <!-- User image -->
                        <li class="user-header">
                            <img src="https://static-00.iconduck.com/assets.00/profile-major-icon-1024x1024-9rtgyx30.png" class="img-circle profil" alt="User Image">
                            <p> <?php echo $userName ?></p>
                            <!-- <small>Login terakhir : 2024-12-03 22:34:36</small> -->
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-right">
                                <a href="logout.php" class="btn btn-default btn-flat">Logout</a>
                            </div>
                        </li>
                    </ul>
            </li>
        </nav>
    </header>
    <section class="res" id="home">
        <div class="res-c">
            <h1>Your Reservation<br>Will be Held at</h1>
        </div>
    </section>
    <?php if (isset($_SESSION['orderDetails']) && !empty($_SESSION['orderDetails'])) {?>
    <section class="rus" id="home">
        <div class="rus-c">
            <fieldset>
                <!-- Reservation Details Section -->
                <div class="billreserv">
                    <div class="iden">
                        <div class="identitasku1">
                            <p><b>Name:</b> <?= htmlspecialchars($userName); ?></p>
                            <br/>
                            <br/>
                            <p><b>Duration:</b> <?= $duration; ?></p>
                        </div>
                        <div class="identitasku2">
                            <?php if ($reservationFound): ?>
                                <p><b>Date:</b> <?= htmlspecialchars($reservationDate); ?></p>
                                <br/>
                                <br/>
                                <p><b>Time:</b> <?= htmlspecialchars($reservationTime); ?></p>
                            <?php else: ?>
                                <p>No reservation details found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- Makanan Section -->
                <div class="menumakanan">
                    <div class="line-container">
                        <div class="line"></div><h2>Makanan</h2><div class="line"></div>
                    </div>

                    <?php foreach ($menuMap as $item): ?>
                    <?php if (strpos(strtolower($item['nama_menu']), 'kwetiau') !== false): ?>
                        <div class="menumyres">
                            <img src="<?= htmlspecialchars($item['foto']); ?>" alt="<?= htmlspecialchars($item['nama_menu']); ?>">
                            <h3><?= htmlspecialchars($item['nama_menu']); ?></h3>
                            <p><?= htmlspecialchars($item['deskripsi']); ?></p>
                            <p class="price">
                                <?= $item['quantity']; ?> x Rp<?= number_format($item['harga'], 2, ',', '.'); ?> =
                                Rp<?= number_format($item['harga'] * $item['quantity'], 2, ',', '.'); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <!-- Minuman Section -->
                <div class="menuminuman">
                    <div class="line-container">
                        <div class="line"></div><h2>Minuman</h2><div class="line"></div>
                    </div>

                    <?php foreach ($menuMap as $item): ?>
                    <?php if (strpos(strtolower($item['nama_menu']), 'kwetiau') === false): ?>
                        <div class="menumyres">
                            <img src="<?= htmlspecialchars($item['foto']); ?>" alt="<?= htmlspecialchars($item['nama_menu']); ?>">
                            <h3><?= htmlspecialchars($item['nama_menu']); ?></h3>
                            <p><?= htmlspecialchars($item['deskripsi']); ?></p>
                            <p class="price">
                                <?= $item['quantity']; ?> x Rp<?= number_format($item['harga'], 2, ',', '.'); ?> =
                                Rp<?= number_format($item['harga'] * $item['quantity'], 2, ',', '.'); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <!-- Total Section -->
                <div class="totalmyres">
                    <div class="line-container">
                        <div class="line"></div><h2>Total</h2><div class="line"></div>
                    </div>
                    <br/>
                    <br/>
                    <p class="price"><b>Total Amount:</b> Rp<?= number_format($totalAmount, 2, ',', '.'); ?></p>
                </div>
            </fieldset>
        </div>
        <div class="deleteupdatebutton">
            <button id="update" class="buttonmyres">Update</button>
            <button id="delete" class="buttonmyres">Delete</button></a>
        </div>

        
        <?php } else {
            header('Location: reservasi.php');
        }?>

<script>
    document.getElementById("update").addEventListener("click", function () {
        window.location.href = "update.php";
    });
</script>

<script>
    document.getElementById("delete").addEventListener("click", function() {
        fetch('delete_order.php', {
            method: 'GET',
        })
        .then(response => {
            if (response.ok) {
                window.location.href = "index.php";
            } else {
                alert("Failed to delete the reservation.");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred while deleting the reservation.");
        });
    });
</script>


</body>
</html>