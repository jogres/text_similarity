<?php
/**
 * Dashboard de Monitoramento do Sistema
 * Interface administrativa para monitorar performance e logs
 * 
 * @version 1.0
 * @author Sistema TCC
 */

require_once '../functions/logger.php';
require_once '../functions/performance.php';
require_once '../functions/cache.php';

// Verificar se é admin (implementar autenticação adequada)
$isAdmin = true; // TODO: Implementar autenticação real

if (!$isAdmin) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acesso negado');
}

$logger = new SystemLogger('../logs/');
$cache = new TextAnalysisCache('../cache/');
$performance = new PerformanceMonitor();

// Função auxiliar para formatar bytes
function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $unitIndex = 0;
    
    while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
        $bytes /= 1024;
        $unitIndex++;
    }
    
    return round($bytes, 2) . ' ' . $units[$unitIndex];
}

// Obter estatísticas
$logStats = $logger->getLogStats(7);
$cacheStats = $cache->getCacheStats();
$performanceStats = $performance->getOverallStats();
$systemLimits = $performance->checkSystemLimits();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Análise de Textos</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #007bff 0%, #0056b3); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; }
        .header h1 { font-size: 2.5rem; margin-bottom: 10px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .stat-card h3 { color: #007bff; margin-bottom: 15px; font-size: 1.3rem; }
        .stat-value { font-size: 2rem; font-weight: bold; color: #28a745; margin-bottom: 10px; }
        .stat-label { color: #6c757d; font-size: 0.9rem; }
        .chart-container { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .chart-container h3 { color: #007bff; margin-bottom: 20px; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 10px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 10px; margin: 10px 0; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 10px; margin: 10px 0; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        .table th { background: #f8f9fa; font-weight: 600; color: #495057; }
        .refresh-btn { position: fixed; bottom: 20px; right: 20px; background: #28a745; color: white; border: none; border-radius: 50%; width: 60px; height: 60px; font-size: 1.5rem; cursor: pointer; box-shadow: 0 5px 15px rgba(40,167,69,0.3); }
        .refresh-btn:hover { background: #1e7e34; transform: scale(1.1); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Dashboard do Sistema</h1>
            <p>Monitoramento em tempo real do sistema de análise de textos</p>
            <div style="margin-top: 12px;">
                <a href="../index.php" class="btn" style="margin-right:6px;">🏠 Início</a>
                <a href="../advanced_analysis.php" class="btn" style="margin-right:6px;">🔍 Análise Avançada</a>
                <a href="../index.php#compare" class="btn">📊 Comparar Textos</a>
            </div>
        </div>

        <!-- Estatísticas Gerais -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>📈 Logs do Sistema</h3>
                <div class="stat-value"><?php echo number_format($logStats['total_logs'] ?? 0); ?></div>
                <div class="stat-label">Total de logs (7 dias)</div>
                <div class="stat-label">Erros hoje: <?php echo $logStats['errors_today'] ?? 0; ?></div>
            </div>

            <div class="stat-card">
                <h3>💾 Cache</h3>
                <div class="stat-value"><?php echo $cacheStats['total_files'] ?? 0; ?></div>
                <div class="stat-label">Arquivos em cache</div>
                <div class="stat-label">Tamanho: <?php echo $cacheStats['total_size'] ?? '0 B'; ?></div>
            </div>

            <div class="stat-card">
                <h3>⚡ Performance</h3>
                <div class="stat-value"><?php echo round($performanceStats['total_execution_time'] ?? 0, 2); ?>s</div>
                <div class="stat-label">Tempo de execução</div>
                <div class="stat-label">Memória: <?php echo $performanceStats['peak_memory_usage'] ?? '0 B'; ?></div>
            </div>

            <div class="stat-card">
                <h3>🔧 Sistema</h3>
                <div class="stat-value"><?php echo $systemLimits['current_usage']['memory'] ?? '0 B'; ?></div>
                <div class="stat-label">Uso atual de memória</div>
                <div class="stat-label">Limite: <?php echo $systemLimits['limits']['memory_limit'] ?? 'N/A'; ?></div>
            </div>
        </div>

        <!-- Avisos do Sistema -->
        <?php if (!empty($systemLimits['warnings'])): ?>
            <div class="chart-container">
                <h3>⚠️ Avisos do Sistema</h3>
                <?php foreach ($systemLimits['warnings'] as $warning): ?>
                    <div class="warning"><?php echo htmlspecialchars($warning); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Estatísticas de Logs por Nível -->
        <div class="chart-container">
            <h3>📊 Logs por Nível (7 dias)</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nível</th>
                        <th>Quantidade</th>
                        <th>Percentual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalLogs = $logStats['total_logs'] ?? 0;
                    $byLevel = $logStats['by_level'] ?? [];
                    
                    if (empty($byLevel)) {
                        echo '<tr><td colspan="3" style="text-align: center; color: #6c757d;">Nenhum log encontrado</td></tr>';
                    } else {
                        foreach ($byLevel as $level => $count): 
                            $percentage = $totalLogs > 0 ? round(($count / $totalLogs) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($level); ?></strong></td>
                        <td><?php echo number_format($count); ?></td>
                        <td><?php echo $percentage; ?>%</td>
                    </tr>
                    <?php 
                        endforeach; 
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Estatísticas de Cache -->
        <div class="chart-container">
            <h3>💾 Estatísticas de Cache</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <h4>Arquivos</h4>
                    <div class="stat-value"><?php echo $cacheStats['total_files'] ?? 0; ?></div>
                </div>
                <div class="stat-card">
                    <h4>Tamanho Total</h4>
                    <div class="stat-value"><?php echo $cacheStats['total_size'] ?? '0 B'; ?></div>
                </div>
                <div class="stat-card">
                    <h4>Arquivo Mais Antigo</h4>
                    <div class="stat-value"><?php echo $cacheStats['oldest_file'] ?? 'N/A'; ?></div>
                </div>
                <div class="stat-card">
                    <h4>Arquivo Mais Recente</h4>
                    <div class="stat-value"><?php echo $cacheStats['newest_file'] ?? 'N/A'; ?></div>
                </div>
            </div>
        </div>

        <!-- Operações de Performance -->
        <div class="chart-container">
            <h3>⚡ Operações de Performance</h3>
            <?php if (!empty($performanceStats['operations'])): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Operação</th>
                        <th>Duração (ms)</th>
                        <th>Memória Usada</th>
                        <th>Pico de Memória</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($performanceStats['operations'] as $operation => $data): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($operation); ?></strong></td>
                        <td><?php echo isset($data['duration']) ? round($data['duration'] * 1000, 2) : 'N/A'; ?></td>
                        <td><?php echo isset($data['memory_used']) ? formatBytes($data['memory_used']) : 'N/A'; ?></td>
                        <td><?php echo isset($data['peak_memory']) ? formatBytes($data['peak_memory']) : 'N/A'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p style="text-align: center; color: #6c757d; padding: 20px;">Nenhuma operação de performance registrada</p>
            <?php endif; ?>
        </div>

        <!-- Ações Administrativas -->
        <div class="chart-container">
            <h3>🔧 Ações Administrativas</h3>
            <a href="?action=clear_cache" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja limpar o cache?')">Limpar Cache</a>
            <a href="?action=clear_logs" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja limpar os logs?')">Limpar Logs</a>
            <a href="?action=export_logs" class="btn">Exportar Logs</a>
            <a href="../index.php" class="btn">Voltar ao Sistema</a>
        </div>
    </div>

    <button class="refresh-btn" onclick="location.reload()" title="Atualizar Dashboard">🔄</button>

    <script>
        // Auto-refresh a cada 30 segundos
        setTimeout(function() {
            location.reload();
        }, 30000);

        // Processar ações administrativas
        <?php
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'clear_cache':
                    // Limpar arquivos de cache (diretório raiz)
                    $cacheFiles = glob('../cache/*.json');
                    if ($cacheFiles) {
                        foreach ($cacheFiles as $file) {
                            @unlink($file);
                        }
                    }
                    echo "location.href='dashboard.php?notice=" . rawurlencode('Cache limpo com sucesso!') . "'";
                    break;
                case 'clear_logs':
                    // Limpar arquivos de logs (arquivos do dia e backups)
                    $logFiles = array_merge(glob('../logs/*.log'), glob('../logs/*.log.*'));
                    if ($logFiles) {
                        foreach ($logFiles as $file) {
                            @unlink($file);
                        }
                    }
                    echo "location.href='dashboard.php?notice=" . rawurlencode('Logs limpos com sucesso!') . "'";
                    break;
                case 'export_logs':
                    // Exportar logs simples: concatenar conteúdo e forçar download
                    $files = glob('../logs/*.log');
                    $export = '';
                    if ($files) {
                        foreach ($files as $f) {
                            $export .= "===== " . basename($f) . " =====\n" . @file_get_contents($f) . "\n\n";
                        }
                    }
                    $filename = 'logs_export_' . date('Ymd_His') . '.txt';
                    header('Content-Type: text/plain');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Content-Length: ' . strlen($export));
                    echo $export;
                    exit;
            }
        }
        ?>
    </script>
</body>
</html>
