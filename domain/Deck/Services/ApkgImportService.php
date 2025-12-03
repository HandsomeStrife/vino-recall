<?php

declare(strict_types=1);

namespace Domain\Deck\Services;

use Domain\Card\Enums\CardType;

class ApkgImportService
{
    /**
     * Parse Anki .apkg file and return cards data
     *
     * Note: This is a simplified implementation. A full implementation would:
     * - Extract the .apkg file (which is a ZIP)
     * - Read the collection.anki2 SQLite database
     * - Parse notes and cards
     * - Extract media files
     *
     * For now, this is a placeholder that throws an exception with instructions
     */
    public function parseAPKG(string $filePath): array
    {
        if (! file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        // Check if file is a valid ZIP
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \InvalidArgumentException("Invalid APKG file: not a valid ZIP archive");
        }

        // Validate all files before extraction to prevent path traversal
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            
            // Check for path traversal attempts
            if (str_contains($filename, '..') || str_starts_with($filename, '/') || str_contains($filename, '\\')) {
                $zip->close();
                throw new \InvalidArgumentException("Invalid APKG file: contains unsafe file paths");
            }
            
            // Check for absolute paths
            if (preg_match('/^[a-zA-Z]:\\\\/', $filename)) {
                $zip->close();
                throw new \InvalidArgumentException("Invalid APKG file: contains absolute paths");
            }
        }

        $cards = [];

        // Look for collection.anki2 or collection.anki21 file
        $collectionFile = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (in_array($filename, ['collection.anki2', 'collection.anki21'])) {
                $collectionFile = $filename;
                break;
            }
        }

        if (! $collectionFile) {
            $zip->close();
            throw new \InvalidArgumentException("Invalid APKG file: no collection database found");
        }

        // Extract to temporary location with restricted permissions
        $tempDir = sys_get_temp_dir() . '/apkg_' . bin2hex(random_bytes(16));
        mkdir($tempDir, 0700); // Restrict permissions to owner only

        $zip->extractTo($tempDir);
        $zip->close();

        $dbPath = $tempDir . '/' . $collectionFile;

        // Validate extracted database file exists and is readable
        if (! file_exists($dbPath) || ! is_readable($dbPath)) {
            $this->deleteDirectory($tempDir);
            throw new \InvalidArgumentException("Invalid APKG file: collection database not found or not readable");
        }

        try {
            // Open SQLite database in read-only mode
            $db = new \SQLite3($dbPath, SQLITE3_OPEN_READONLY);
            $db->busyTimeout(5000);

            // Use prepared statements for safety
            $stmt = $db->prepare("SELECT flds, tags FROM notes LIMIT :limit");
            $stmt->bindValue(':limit', 1000, SQLITE3_INTEGER);
            $result = $stmt->execute();

            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $fields = explode("\x1f", $row['flds']);

                // Assume first field is question, second is answer (basic card type)
                if (count($fields) >= 2) {
                    $cards[] = [
                        'question' => $this->sanitizeHtml($fields[0]),
                        'answer' => $this->sanitizeHtml($fields[1]),
                        'image_path' => null, // Media extraction not implemented
                        'card_type' => CardType::TRADITIONAL,
                        'answer_choices' => null,
                        'correct_answer_index' => null,
                    ];
                }
            }

            $db->close();
        } catch (\Exception $e) {
            $this->deleteDirectory($tempDir);
            \Log::error("APKG database parsing error: {$e->getMessage()}");
            throw new \RuntimeException("Invalid APKG database format");
        }

        // Cleanup
        $this->deleteDirectory($tempDir);

        return $cards;
    }

    /**
     * Sanitize HTML content
     */
    private function sanitizeHtml(string $content): string
    {
        // Remove HTML tags and decode entities
        $content = strip_tags($content);
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Limit length to prevent DoS
        return substr($content, 0, 5000);
    }

    /**
     * Recursively delete a directory
     */
    private function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    /**
     * Validate card data
     */
    public function validate(array $cardData): bool
    {
        if (empty($cardData['question']) || empty($cardData['answer'])) {
            return false;
        }

        return true;
    }
}

