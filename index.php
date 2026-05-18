<?php
require_once 'db.php';
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kampüs Etkinlik ve Kulüp Yönetim Sistemi</title>
</head>
<body>

<header>
    <nav>
        <ul>
            <li><a href="index.php">Ana Sayfa</a></li>
            <li><a href="etkinlikler.php">Etkinlikler</a></li>
            <li><a href="kulüpler.php">Kulüpler</a></li>
            <li><a href="profilim.php">Profilim</a></li>
            <li><a href="rapor.php">Rapor</a></li>
            <li><a href="giris.php">Giriş Yap</a></li>
            <li><a href="kayit.php">Kayıt Ol</a></li>
        </ul>
    </nav>
    <h1>Kampüs Etkinlik ve Kulüp Yönetim Sistemi</h1>
    <p>Etkinlikleri keşfet, kulüplere katıl, anılarını oluştur!</p>
</header>

<main>

    <!-- ETKİNLİK LİSTESİ + FİLTRE -->
    <section>
        <h2>Yaklaşan Etkinlikler</h2>

        <!-- Filtre Formu (GET ile) -->
        <form method="GET" action="index.php">
            <input type="text" name="search" placeholder="Etkinlik ara...">
            <select name="club_id">
                <option value="">Tüm Kulüpler</option>
                <?php
                $kulupSorgu = $pdo->query("SELECT * FROM clubs");
                $kulupList = $kulupSorgu->fetchAll();
                foreach ($kulupList as $kulup):
                    $selected = (isset($_GET['club_id']) && $_GET['club_id'] == $kulup['id']) ? 'selected' : '';
                ?>
                    <option value="<?php echo $kulup['id']; ?>" <?php echo $selected; ?>>
                        <?php echo htmlspecialchars($kulup['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="start_date" value="<?php echo $_GET['start_date'] ?? ''; ?>">
            <input type="date" name="end_date" value="<?php echo $_GET['end_date'] ?? ''; ?>">
            <select name="sort">
                <option value="date" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'date') ? 'selected' : ''; ?>>Tarihe Göre</option>
                <option value="popular" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'popular') ? 'selected' : ''; ?>>Popülerliğe Göre</option>
            </select>
            <button type="submit">Ara</button>
        </form>

        <!-- Etkinlik Listesi (Dinamik) -->
        <div>
            <?php
            // Filtreleme ve sıralama için SQL hazırlığı
            $sql = "SELECT e.*, c.name as club_name,
                    (SELECT COUNT(*) FROM registrations WHERE event_id = e.id) as katilim_sayisi
                    FROM events e
                    JOIN clubs c ON e.club_id = c.id
                    WHERE 1=1";

            $params = [];

            // Arama (başlık/lokasyon)
            if (!empty($_GET['search'])) {
                $sql .= " AND (e.title LIKE ? OR e.location LIKE ?)";
                $search = "%" . $_GET['search'] . "%";
                $params[] = $search;
                $params[] = $search;
            }

            // Kulüp filtresi
            if (!empty($_GET['club_id'])) {
                $sql .= " AND e.club_id = ?";
                $params[] = $_GET['club_id'];
            }

            // Tarih aralığı filtresi
            if (!empty($_GET['start_date'])) {
                $sql .= " AND e.event_date >= ?";
                $params[] = $_GET['start_date'] . " 00:00:00";
            }
            if (!empty($_GET['end_date'])) {
                $sql .= " AND e.event_date <= ?";
                $params[] = $_GET['end_date'] . " 23:59:59";
            }

            // Sıralama
            $sort = $_GET['sort'] ?? 'date';
            if ($sort == 'popular') {
                $sql .= " ORDER BY katilim_sayisi DESC";
            } else {
                $sql .= " ORDER BY e.event_date ASC";
            }

            $sorgu = $pdo->prepare($sql);
            $sorgu->execute($params);
            $etkinlikler = $sorgu->fetchAll();

            if (count($etkinlikler) > 0):
                foreach ($etkinlikler as $etkinlik):
            ?>
                <div>
                    <h3><?php echo htmlspecialchars($etkinlik['club_name']); ?>: <?php echo htmlspecialchars($etkinlik['title']); ?></h3>
                    <p>Tarih: <?php echo date('d.m.Y H:i', strtotime($etkinlik['event_date'])); ?></p>
                    <p>Yer: <?php echo htmlspecialchars($etkinlik['location']); ?></p>
                    <p>Katılımcı: <?php echo $etkinlik['katilim_sayisi']; ?> / <?php echo $etkinlik['quota']; ?></p>
                    <a href="etkinlikdetay.php?id=<?php echo $etkinlik['id']; ?>">Detaylar</a>
                </div>
            <?php
                endforeach;
            else:
            ?>
                <p>Gösterilecek etkinlik bulunamadı.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- KULÜP LİSTESİ -->
    <section>
        <h2>Kulüplerimiz</h2>
        <div>
            <?php
            $sorgu = $pdo->query("SELECT * FROM clubs");
            $kulüpler = $sorgu->fetchAll();
            foreach ($kulüpler as $kulup):
            ?>
                <div>
                    <h3><?php echo htmlspecialchars($kulup['name']); ?></h3>
                    <p><?php echo htmlspecialchars($kulup['description']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

</main>

<footer>
    <p>&copy; 2026 - Kampüs Etkinlik ve Kulüp Yönetim Sistemi</p>
    <p><a href="iletisim.php">İletişim</a> | <a href="admin.php">Admin</a></p>
</footer>

</body>
</html>