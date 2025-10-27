<?php
namespace App\Services;

class BackupService {
    private $db;
    private $config;
    private $backupPath;
    
    public function __construct() {
        $this->db = \App\Core\Database::getInstance();
        $this->config = require __DIR__ . '/../../config/app.php';
        $this->backupPath = __DIR__ . '/../../storage/backups/';
    }
    
    public function create() {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_{$timestamp}.zip";
        $zipPath = $this->backupPath . $filename;
        
        try {
            // Créer le dossier de backup si nécessaire
            if (!is_dir($this->backupPath)) {
                mkdir($this->backupPath, 0755, true);
            }
            
            // Créer l'archive ZIP
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
                throw new \Exception("Impossible de créer l'archive ZIP");
            }
            
            // Sauvegarder la base de données
            $this->backupDatabase($zip);
            
            // Sauvegarder les fichiers importants
            $this->backupFiles($zip);
            
            $zip->close();
            
            // Logger le backup
            \App\Core\Logger::info('backup.created', [
                'filename' => $filename,
                'size' => filesize($zipPath)
            ]);
            
            return $filename;
        } catch (\Exception $e) {
            if (isset($zip)) {
                $zip->close();
            }
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }
            throw $e;
        }
    }
    
    private function backupDatabase($zip) {
        $tables = $this->db->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        $sql = "-- Database backup " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($tables as $table) {
            // Structure
            $createTable = $this->db->query("SHOW CREATE TABLE `{$table}`")
                                  ->fetch(\PDO::FETCH_ASSOC);
            $sql .= $createTable['Create Table'] . ";\n\n";
            
            // Données
            $rows = $this->db->query("SELECT * FROM `{$table}`")
                            ->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($rows as $row) {
                $values = array_map(function($value) {
                    return $value === null ? 'NULL' : 
                           $this->db->quote($value);
                }, $row);
                
                $sql .= "INSERT INTO `{$table}` VALUES (" . 
                        implode(', ', $values) . ");\n";
            }
            
            $sql .= "\n";
        }
        
        $zip->addFromString('database.sql', $sql);
    }
    
    private function backupFiles($zip) {
        $dirsToBackup = [
            'config',
            'storage/app',
            'storage/uploads',
            'public/assets'
        ];
        
        foreach ($dirsToBackup as $dir) {
            $fullPath = __DIR__ . '/../../' . $dir;
            if (!is_dir($fullPath)) continue;
            
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($fullPath),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            foreach ($iterator as $file) {
                if ($file->isDir()) continue;
                
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen(__DIR__ . '/../../'));
                
                $zip->addFile($filePath, $relativePath);
            }
        }
    }
    
    public function restore($filename) {
        $zipPath = $this->backupPath . $filename;
        
        if (!file_exists($zipPath)) {
            throw new \Exception("Fichier de backup introuvable");
        }
        
        try {
            // Extraire l'archive
            $zip = new \ZipArchive();
            if ($zip->open($zipPath) !== true) {
                throw new \Exception("Impossible d'ouvrir l'archive");
            }
            
            // Restaurer la base de données
            $sql = $zip->getFromName('database.sql');
            if ($sql === false) {
                throw new \Exception("Fichier SQL manquant dans l'archive");
            }
            
            // Exécuter les requêtes SQL
            $this->db->beginTransaction();
            try {
                foreach (explode(";\n", $sql) as $query) {
                    $query = trim($query);
                    if (!empty($query)) {
                        $this->db->exec($query);
                    }
                }
                $this->db->commit();
            } catch (\Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
            
            // Restaurer les fichiers
            $tempDir = sys_get_temp_dir() . '/restore_' . uniqid();
            mkdir($tempDir);
            
            $zip->extractTo($tempDir);
            $zip->close();
            
            // Copier les fichiers vers leur emplacement d'origine
            $this->restoreFiles($tempDir);
            
            // Nettoyer
            $this->removeDirectory($tempDir);
            
            // Logger la restauration
            \App\Core\Logger::info('backup.restored', [
                'filename' => $filename
            ]);
            
            return true;
        } catch (\Exception $e) {
            if (isset($tempDir) && is_dir($tempDir)) {
                $this->removeDirectory($tempDir);
            }
            throw $e;
        }
    }
    
    private function restoreFiles($tempDir) {
        $dirsToRestore = [
            'config',
            'storage/app',
            'storage/uploads',
            'public/assets'
        ];
        
        foreach ($dirsToRestore as $dir) {
            $sourcePath = $tempDir . '/' . $dir;
            $targetPath = __DIR__ . '/../../' . $dir;
            
            if (!is_dir($sourcePath)) continue;
            
            // Sauvegarder les fichiers existants
            if (is_dir($targetPath)) {
                rename($targetPath, $targetPath . '_backup_' . time());
            }
            
            // Copier les nouveaux fichiers
            mkdir($targetPath, 0755, true);
            $this->copyDirectory($sourcePath, $targetPath);
        }
    }
    
    private function copyDirectory($source, $target) {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                mkdir($target . '/' . $iterator->getSubPathName());
            } else {
                copy($item, $target . '/' . $iterator->getSubPathName());
            }
        }
    }
    
    private function removeDirectory($dir) {
        if (!is_dir($dir)) return;
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item);
            } else {
                unlink($item);
            }
        }
        
        rmdir($dir);
    }
    
    public function getBackupsList() {
        if (!is_dir($this->backupPath)) {
            return [];
        }
        
        $backups = [];
        foreach (scandir($this->backupPath) as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $path = $this->backupPath . $file;
            $backups[] = [
                'filename' => $file,
                'size' => filesize($path),
                'created_at' => filemtime($path)
            ];
        }
        
        usort($backups, function($a, $b) {
            return $b['created_at'] - $a['created_at'];
        });
        
        return $backups;
    }
    
    public function deleteBackup($filename) {
        $path = $this->backupPath . $filename;
        
        if (!file_exists($path)) {
            throw new \Exception("Fichier de backup introuvable");
        }
        
        unlink($path);
        
        \App\Core\Logger::info('backup.deleted', [
            'filename' => $filename
        ]);
        
        return true;
    }
}
?>
