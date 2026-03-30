<?php
/**
 * Stats Helper - Visits & Downloads tracking
 */
class Stats {

    public static function recordVisit(): void {
        $db  = Database::get();
        $ip  = self::getClientIP();
        $ua  = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

        // Unique visit per IP per 24h
        $stmt = $db->prepare(
            "SELECT id FROM visits
             WHERE ip_address = ? AND visited_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
             LIMIT 1"
        );
        $stmt->execute([$ip]);
        if (!$stmt->fetch()) {
            $db->prepare(
                "INSERT INTO visits (ip_address, user_agent) VALUES (?, ?)"
            )->execute([$ip, $ua]);
        }
    }

    public static function recordDownload(string $fileName, string $noteName): void {
        $db = Database::get();
        $ip = self::getClientIP();
        $db->prepare(
            "INSERT INTO downloads (file_name, note_name, ip_address) VALUES (?, ?, ?)"
        )->execute([$fileName, $noteName, $ip]);
    }

    public static function getTotalVisits(): int {
        $r = Database::get()->query("SELECT COUNT(*) FROM visits")->fetchColumn();
        return (int) $r;
    }

    public static function getTotalDownloads(): int {
        $r = Database::get()->query("SELECT COUNT(*) FROM downloads")->fetchColumn();
        return (int) $r;
    }

    public static function getTotalNotes(): int {
        $db     = Database::get();
        $tables = self::getNoteTables($db);
        $total  = 0;
        foreach ($tables as $t) {
            $count = $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
            $total += (int) $count;
        }
        return $total;
    }

    public static function getTopDownloaded(int $limit = 5): array {
        return Database::get()->prepare(
            "SELECT note_name, COUNT(*) as cnt FROM downloads
             GROUP BY note_name ORDER BY cnt DESC LIMIT ?"
        )->execute([$limit]) ? [] : [];
        // proper version:
        $stmt = Database::get()->prepare(
            "SELECT note_name, COUNT(*) as cnt FROM downloads
             GROUP BY note_name ORDER BY cnt DESC LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public static function getVisitStats(): array {
        $db = Database::get();
        return [
            'today'   => (int)$db->query("SELECT COUNT(*) FROM visits WHERE DATE(visited_at) = CURDATE()")->fetchColumn(),
            'week'    => (int)$db->query("SELECT COUNT(*) FROM visits WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn(),
            'month'   => (int)$db->query("SELECT COUNT(*) FROM visits WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn(),
            'total'   => self::getTotalVisits(),
        ];
    }

    private static function getClientIP(): string {
        $headers = ['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'];
        foreach ($headers as $h) {
            if (!empty($_SERVER[$h])) {
                $ip = trim(explode(',', $_SERVER[$h])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
        return '0.0.0.0';
    }

    private static function getNoteTables(PDO $db): array {
        $skip = ['visits','downloads','admin_log','settings'];
        $rows = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        return array_filter($rows, fn($t) => !in_array($t, $skip));
    }
}
