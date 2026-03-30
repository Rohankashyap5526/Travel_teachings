<?php
/**
 * Notes Controller
 */
class Notes {

    private static array $skipTables = ['visits','downloads','admin_log','settings'];

    public static function getCategories(): array {
        $db   = Database::get();
        $rows = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        return array_values(array_filter($rows, fn($t) => !in_array($t, self::$skipTables)));
    }

    public static function getByCategory(string $cat): array {
        if (!self::categoryExists($cat)) return [];
        $stmt = Database::get()->prepare("SELECT * FROM `$cat` ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getAllGrouped(): array {
        $result = [];
        foreach (self::getCategories() as $cat) {
            $result[$cat] = self::getByCategory($cat);
        }
        return $result;
    }

    public static function addNote(string $catName, string $displayName, string $fileName): bool {
        if (!self::categoryExists($catName)) return false;
        $stmt = Database::get()->prepare(
            "INSERT INTO `$catName` (pgf_name, notes_name) VALUES (?, ?)"
        );
        return $stmt->execute([$displayName, $fileName]);
    }

    public static function deleteNote(string $catName, string $pgfName): bool {
        if (!self::categoryExists($catName)) return false;
        // Also remove file
        $stmt = Database::get()->prepare(
            "SELECT notes_name FROM `$catName` WHERE pgf_name = ? LIMIT 1"
        );
        $stmt->execute([$pgfName]);
        $row = $stmt->fetch();
        if ($row) {
            $path = UPLOAD_DIR . $row['notes_name'];
            if (file_exists($path)) @unlink($path);
        }
        $stmt = Database::get()->prepare("DELETE FROM `$catName` WHERE pgf_name = ?");
        return $stmt->execute([$pgfName]);
    }

    public static function addCategory(string $name): bool {
        // Strict name validation
        if (!preg_match('/^[a-zA-Z0-9_]{2,50}$/', $name)) return false;
        $db = Database::get();
        $db->exec("CREATE TABLE IF NOT EXISTS `$name` (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            pgf_name   VARCHAR(255) NOT NULL,
            notes_name VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        return true;
    }

    public static function deleteCategory(string $name): bool {
        if (!self::categoryExists($name)) return false;
        // Delete all files first
        $stmt = Database::get()->prepare("SELECT notes_name FROM `$name`");
        $stmt->execute();
        foreach ($stmt->fetchAll() as $row) {
            $path = UPLOAD_DIR . $row['notes_name'];
            if (file_exists($path)) @unlink($path);
        }
        Database::get()->exec("DROP TABLE IF EXISTS `$name`");
        return true;
    }

    public static function categoryExists(string $name): bool {
        // Prevent SQL injection via whitelist check
        return in_array($name, self::getCategories(), true);
    }

    public static function getNotePath(string $fileName): ?string {
        // Only alphanumeric + underscore + hyphen + dot
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $fileName)) return null;
        $path = UPLOAD_DIR . $fileName;
        return (file_exists($path) && is_file($path)) ? $path : null;
    }

    // For RAG chatbot - builds a text context from PDF names/categories
    public static function buildContextForAI(): string {
        $grouped = self::getAllGrouped();
        $lines   = [];
        foreach ($grouped as $cat => $notes) {
            $lines[] = "Category: $cat";
            foreach ($notes as $n) {
                $lines[] = "  - " . $n['pgf_name'] . " (file: " . $n['notes_name'] . ")";
            }
        }
        return implode("\n", $lines);
    }
}
