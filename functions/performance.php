<?php
/**
 * Sistema de Monitoramento de Performance
 * Monitora e otimiza performance do sistema
 * 
 * @version 1.0
 * @author Sistema TCC
 */

class PerformanceMonitor {
    private $metrics = [];
    private $startTime;
    private $startMemory;
    
    public function __construct() {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage(true);
    }
    
    /**
     * Iniciar medição de operação
     */
    public function startOperation($operationName) {
        $this->metrics[$operationName] = [
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(true),
            'peak_memory' => memory_get_usage(true)
        ];
    }
    
    /**
     * Finalizar medição de operação
     */
    public function endOperation($operationName) {
        if (!isset($this->metrics[$operationName])) {
            return null;
        }
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $this->metrics[$operationName]['end_time'] = $endTime;
        $this->metrics[$operationName]['end_memory'] = $endMemory;
        $this->metrics[$operationName]['duration'] = $endTime - $this->metrics[$operationName]['start_time'];
        $this->metrics[$operationName]['memory_used'] = $endMemory - $this->metrics[$operationName]['start_memory'];
        $this->metrics[$operationName]['peak_memory'] = max(
            $this->metrics[$operationName]['peak_memory'],
            memory_get_peak_usage(true)
        );
        
        return $this->metrics[$operationName];
    }
    
    /**
     * Obter estatísticas gerais
     */
    public function getOverallStats() {
        $totalTime = microtime(true) - $this->startTime;
        $currentMemory = memory_get_usage(true);
        $peakMemory = memory_get_peak_usage(true);
        
        return [
            'total_execution_time' => round($totalTime, 4),
            'current_memory_usage' => $this->formatBytes($currentMemory),
            'peak_memory_usage' => $this->formatBytes($peakMemory),
            'operations_count' => count($this->metrics),
            'operations' => $this->metrics,
            'memory_limit' => ini_get('memory_limit'),
            'execution_time_limit' => ini_get('max_execution_time')
        ];
    }
    
    /**
     * Detectar gargalos de performance
     */
    public function detectBottlenecks() {
        $bottlenecks = [];
        
        foreach ($this->metrics as $operation => $data) {
            if (isset($data['duration'])) {
                // Operações que demoram mais de 1 segundo
                if ($data['duration'] > 1.0) {
                    $bottlenecks[] = [
                        'operation' => $operation,
                        'type' => 'slow_operation',
                        'duration' => $data['duration'],
                        'severity' => $data['duration'] > 5.0 ? 'high' : 'medium'
                    ];
                }
                
                // Operações que usam muita memória (> 50MB)
                if (isset($data['memory_used']) && $data['memory_used'] > 50 * 1024 * 1024) {
                    $bottlenecks[] = [
                        'operation' => $operation,
                        'type' => 'high_memory',
                        'memory_used' => $this->formatBytes($data['memory_used']),
                        'severity' => $data['memory_used'] > 100 * 1024 * 1024 ? 'high' : 'medium'
                    ];
                }
            }
        }
        
        return $bottlenecks;
    }
    
    /**
     * Sugerir otimizações
     */
    public function getOptimizationSuggestions() {
        $suggestions = [];
        $bottlenecks = $this->detectBottlenecks();
        
        foreach ($bottlenecks as $bottleneck) {
            switch ($bottleneck['type']) {
                case 'slow_operation':
                    $suggestions[] = [
                        'operation' => $bottleneck['operation'],
                        'suggestion' => 'Consider implementing caching for this operation',
                        'priority' => $bottleneck['severity'] === 'high' ? 'high' : 'medium'
                    ];
                    break;
                    
                case 'high_memory':
                    $suggestions[] = [
                        'operation' => $bottleneck['operation'],
                        'suggestion' => 'Consider processing data in chunks or using streaming',
                        'priority' => $bottleneck['severity'] === 'high' ? 'high' : 'medium'
                    ];
                    break;
            }
        }
        
        return $suggestions;
    }
    
    /**
     * Verificar limites do sistema
     */
    public function checkSystemLimits() {
        $limits = [
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size')
        ];
        
        $warnings = [];
        
        // Verificar uso de memória
        $currentMemory = memory_get_usage(true);
        $memoryLimit = $this->parsePhpIniSize($limits['memory_limit']);
        $memoryUsagePercent = $memoryLimit > 0 ? ($currentMemory / $memoryLimit) * 100 : 0;
        
        if ($memoryUsagePercent > 80) {
            $warnings[] = "High memory usage: {$memoryUsagePercent}% of limit";
        }
        
        // Verificar tempo de execução
        $executionTime = microtime(true) - $this->startTime;
        $maxExecutionTime = (int) $limits['max_execution_time'];
        
        if ($maxExecutionTime > 0 && $executionTime > ($maxExecutionTime * 0.8)) {
            $warnings[] = "Approaching execution time limit: {$executionTime}s of {$maxExecutionTime}s";
        }
        
        return [
            'limits' => $limits,
            'current_usage' => [
                'memory' => $this->formatBytes($currentMemory),
                'execution_time' => round($executionTime, 2)
            ],
            'warnings' => $warnings
        ];
    }

    /**
     * Interpretar valores como 128M, 1G do php.ini em bytes
     */
    private function parsePhpIniSize($value) {
        if ($value === false || $value === null || $value === '') {
            return 0;
        }
        $value = trim($value);
        $last = strtolower(substr($value, -1));
        $number = (int)$value;
        switch ($last) {
            case 'g':
                $number *= 1024;
                // no break
            case 'm':
                $number *= 1024;
                // no break
            case 'k':
                $number *= 1024;
        }
        return $number;
    }
    
    /**
     * Gerar relatório de performance
     */
    public function generateReport() {
        $stats = $this->getOverallStats();
        $bottlenecks = $this->detectBottlenecks();
        $suggestions = $this->getOptimizationSuggestions();
        $systemLimits = $this->checkSystemLimits();
        
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'overall_stats' => $stats,
            'bottlenecks' => $bottlenecks,
            'optimization_suggestions' => $suggestions,
            'system_limits' => $systemLimits,
            'performance_score' => $this->calculatePerformanceScore($stats, $bottlenecks)
        ];
    }
    
    /**
     * Calcular score de performance (0-100)
     */
    private function calculatePerformanceScore($stats, $bottlenecks) {
        $score = 100;
        
        // Penalizar por operações lentas
        foreach ($bottlenecks as $bottleneck) {
            if ($bottleneck['type'] === 'slow_operation') {
                $score -= $bottleneck['duration'] * 10; // -10 pontos por segundo
            } elseif ($bottleneck['type'] === 'high_memory') {
                $score -= 20; // -20 pontos por alto uso de memória
            }
        }
        
        // Penalizar por tempo total de execução
        if ($stats['total_execution_time'] > 10) {
            $score -= ($stats['total_execution_time'] - 10) * 2;
        }
        
        return max(0, min(100, round($score)));
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
