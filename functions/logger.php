<?php
/**
 * Sistema de Logs Avançado
 * Registra atividades, erros e métricas de performance
 * 
 * @version 1.0
 * @author Sistema TCC
 */

class SystemLogger {
    private $logDir;
    private $logLevel;
    private $maxLogSize;
    
    const LEVELS = [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
        'CRITICAL' => 4
    ];
    
    public function __construct($logDir = 'logs/', $level = 'INFO', $maxSize = '10MB') {
        $this->logDir = $logDir;
        $this->logLevel = self::LEVELS[$level] ?? self::LEVELS['INFO'];
        $this->maxLogSize = $this->parseSize($maxSize);
        
        // Criar diretório de logs se não existir
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }
    
    /**
     * Registrar log
     */
    public function log($level, $message, $context = []) {
        if (self::LEVELS[$level] < $this->logLevel) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = [
            'timestamp' => $timestamp,
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
        
        $logFile = $this->logDir . date('Y-m-d') . '.log';
        $logLine = json_encode($logEntry) . "\n";
        
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
        
        // Rotacionar log se necessário
        $this->rotateLogIfNeeded($logFile);
    }
    
    /**
     * Log de performance
     */
    public function logPerformance($operation, $startTime, $endTime, $details = []) {
        $duration = $endTime - $startTime;
        $memoryUsed = memory_get_peak_usage(true) - memory_get_usage(true);
        
        $this->log('INFO', "Performance: {$operation}", [
            'duration_ms' => round($duration * 1000, 2),
            'memory_used' => $this->formatBytes($memoryUsed),
            'details' => $details
        ]);
    }
    
    /**
     * Log de erro
     */
    public function logError($message, $exception = null, $context = []) {
        $errorContext = $context;
        
        if ($exception) {
            $errorContext['exception'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];
        }
        
        $this->log('ERROR', $message, $errorContext);
    }
    
    /**
     * Log de análise de texto
     */
    public function logTextAnalysis($textLength, $analysisType, $result, $options = []) {
        $this->log('INFO', "Text analysis completed", [
            'text_length' => $textLength,
            'analysis_type' => $analysisType,
            'similarity' => $result['similarity'] ?? null,
            'processing_time' => $result['processing_time'] ?? null,
            'options' => $options
        ]);
    }
    
    /**
     * Rotacionar log se necessário
     */
    private function rotateLogIfNeeded($logFile) {
        if (file_exists($logFile) && filesize($logFile) > $this->maxLogSize) {
            $backupFile = $logFile . '.' . time() . '.bak';
            rename($logFile, $backupFile);
            
            // Manter apenas os 5 logs mais recentes
            $this->cleanupOldLogs();
        }
    }
    
    /**
     * Limpar logs antigos
     */
    private function cleanupOldLogs() {
        $logFiles = glob($this->logDir . '*.log.*');
        usort($logFiles, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        // Manter apenas os 5 mais recentes
        $toDelete = array_slice($logFiles, 5);
        foreach ($toDelete as $file) {
            unlink($file);
        }
    }
    
    /**
     * Obter estatísticas de logs
     */
    public function getLogStats($days = 7) {
        $stats = [
            'total_logs' => 0,
            'by_level' => [],
            'by_day' => [],
            'errors_today' => 0
        ];
        
        // Verificar se o diretório de logs existe
        if (!is_dir($this->logDir)) {
            return $stats;
        }
        
        $endDate = new DateTime();
        $startDate = (new DateTime())->modify("-{$days} days");
        
        for ($date = clone $startDate; $date <= $endDate; $date->modify('+1 day')) {
            $logFile = $this->logDir . $date->format('Y-m-d') . '.log';
            $dayStats = $this->analyzeLogFile($logFile);
            
            $stats['total_logs'] += $dayStats['total'];
            $stats['by_day'][$date->format('Y-m-d')] = $dayStats;
            
            foreach ($dayStats['by_level'] as $level => $count) {
                $stats['by_level'][$level] = ($stats['by_level'][$level] ?? 0) + $count;
            }
        }
        
        // Contar erros de hoje
        $todayLog = $this->logDir . date('Y-m-d') . '.log';
        if (file_exists($todayLog)) {
            $todayStats = $this->analyzeLogFile($todayLog);
            $stats['errors_today'] = $todayStats['by_level']['ERROR'] ?? 0;
        }
        
        return $stats;
    }
    
    /**
     * Analisar arquivo de log
     */
    private function analyzeLogFile($logFile) {
        $stats = ['total' => 0, 'by_level' => []];
        
        if (!file_exists($logFile)) {
            return $stats;
        }
        
        $lines = file($logFile, FILE_IGNORE_NEW_LINES);
        foreach ($lines as $line) {
            $logEntry = json_decode($line, true);
            if ($logEntry && isset($logEntry['level'])) {
                $stats['total']++;
                $level = $logEntry['level'];
                $stats['by_level'][$level] = ($stats['by_level'][$level] ?? 0) + 1;
            }
        }
        
        return $stats;
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
