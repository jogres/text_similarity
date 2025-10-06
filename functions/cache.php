<?php
/**
 * Sistema de Cache Inteligente
 * Otimiza performance através de cache de resultados
 * 
 * @version 1.0
 * @author Sistema TCC
 */

class TextAnalysisCache {
    private $cacheDir;
    private $maxCacheSize;
    private $cacheExpiry;
    
    public function __construct($cacheDir = 'cache/', $maxSize = '100MB', $expiry = 3600) {
        $this->cacheDir = $cacheDir;
        $this->maxCacheSize = $this->parseSize($maxSize);
        $this->cacheExpiry = $expiry;
        
        // Criar diretório de cache se não existir
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * Gerar hash único para o texto
     */
    private function generateHash($text, $options = []) {
        $data = $text . serialize($options);
        return hash('sha256', $data);
    }
    
    /**
     * Verificar se cache existe e é válido
     */
    public function hasCache($text, $options = []) {
        $hash = $this->generateHash($text, $options);
        $cacheFile = $this->cacheDir . $hash . '.json';
        
        if (!file_exists($cacheFile)) {
            return false;
        }
        
        // Verificar se não expirou
        if (time() - filemtime($cacheFile) > $this->cacheExpiry) {
            unlink($cacheFile);
            return false;
        }
        
        return true;
    }
    
    /**
     * Obter resultado do cache
     */
    public function getCache($text, $options = []) {
        $hash = $this->generateHash($text, $options);
        $cacheFile = $this->cacheDir . $hash . '.json';
        
        if (file_exists($cacheFile)) {
            $content = file_get_contents($cacheFile);
            $data = json_decode($content, true);
            
            if ($data && isset($data['result'])) {
                // Adicionar flag de cache
                $data['result']['from_cache'] = true;
                $data['result']['cache_timestamp'] = filemtime($cacheFile);
                return $data['result'];
            }
        }
        
        return null;
    }
    
    /**
     * Salvar resultado no cache
     */
    public function setCache($text, $options = [], $result) {
        $hash = $this->generateHash($text, $options);
        $cacheFile = $this->cacheDir . $hash . '.json';
        
        $data = [
            'text_hash' => $hash,
            'options' => $options,
            'result' => $result,
            'created_at' => time()
        ];
        
        file_put_contents($cacheFile, json_encode($data, JSON_PRETTY_PRINT));
        
        // Limpar cache se necessário
        $this->cleanupCache();
    }
    
    /**
     * Limpar cache antigo
     */
    private function cleanupCache() {
        $files = glob($this->cacheDir . '*.json');
        $totalSize = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
        }
        
        if ($totalSize > $this->maxCacheSize) {
            // Ordenar por data de modificação (mais antigos primeiro)
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            // Remover 30% dos arquivos mais antigos
            $toRemove = array_slice($files, 0, intval(count($files) * 0.3));
            foreach ($toRemove as $file) {
                unlink($file);
            }
        }
    }
    
    /**
     * Converter string de tamanho para bytes
     */
    private function parseSize($size) {
        $units = ['B' => 1, 'KB' => 1024, 'MB' => 1024*1024, 'GB' => 1024*1024*1024];
        $size = strtoupper(trim($size));
        
        foreach ($units as $unit => $multiplier) {
            if (strpos($size, $unit) !== false) {
                $number = (float) str_replace($unit, '', $size);
                return $number * $multiplier;
            }
        }
        
        return 1024 * 1024; // 1MB padrão
    }
    
    /**
     * Estatísticas do cache
     */
    public function getCacheStats() {
        // Verificar se o diretório de cache existe
        if (!is_dir($this->cacheDir)) {
            return [
                'total_files' => 0,
                'total_size' => '0 B',
                'oldest_file' => null,
                'newest_file' => null
            ];
        }
        
        $files = glob($this->cacheDir . '*.json');
        $totalSize = 0;
        $oldestFile = null;
        $newestFile = null;
        
        if (empty($files)) {
            return [
                'total_files' => 0,
                'total_size' => '0 B',
                'oldest_file' => null,
                'newest_file' => null
            ];
        }
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $size = filesize($file);
                $totalSize += $size;
                
                $mtime = filemtime($file);
                if (!$oldestFile || $mtime < filemtime($oldestFile)) {
                    $oldestFile = $file;
                }
                if (!$newestFile || $mtime > filemtime($newestFile)) {
                    $newestFile = $file;
                }
            }
        }
        
        return [
            'total_files' => count($files),
            'total_size' => $this->formatBytes($totalSize),
            'oldest_file' => $oldestFile ? date('Y-m-d H:i:s', filemtime($oldestFile)) : null,
            'newest_file' => $newestFile ? date('Y-m-d H:i:s', filemtime($newestFile)) : null
        ];
    }
    
    /**
     * Formatar bytes para string legível
     */
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}
