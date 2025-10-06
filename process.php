<?php
/**
 * Sistema de Comparação de Conteúdo de Textos
 * Processamento avançado com validações e tratamento de erros
 * 
 * @version 2.0
 * @author Sistema TCC
 */
// Incluir arquivos de funções
require_once 'functions/similarity.php';
require_once 'functions/file_processor.php';
require_once 'functions/cache.php';
require_once 'functions/logger.php';
require_once 'functions/performance.php';
require_once 'config/system.php';
// Carregar configurações do sistema e mapear para chaves esperadas com defaults seguros
$systemConfig = require 'config/system.php';
$perf = $systemConfig['performance'] ?? [];
$config = [
    'maxTextLength' => $perf['max_text_length'] ?? 500000,
    'minTextLength' => $perf['min_text_length'] ?? 10,
    'maxProcessingTime' => $perf['max_processing_time'] ?? 600,
    'memoryLimit' => $perf['memory_limit'] ?? (ini_get('memory_limit') ?: '512M'),
    'executionTimeLimit' => $perf['execution_time_limit'] ?? ((int) (ini_get('max_execution_time') ?: 600))
];

// Inicializar sistemas de monitoramento
$logger = new SystemLogger();
$cache = new TextAnalysisCache();
$performance = new PerformanceMonitor();
// Configurar limites de execução (apenas se válidos)
if (!empty($config['executionTimeLimit']) && (int)$config['executionTimeLimit'] > 0) {
    @set_time_limit((int)$config['executionTimeLimit']);
}
if (!empty($config['memoryLimit'])) {
    @ini_set('memory_limit', (string)$config['memoryLimit']);
}
// Verificar se o método é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
// Função para sanitizar texto
function sanitizeText($text) {
    // Remover caracteres de controle e normalizar espaços
    $text = preg_replace('/[\x00-\x1F\x7F]/', '', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text);
}
// Função para validar texto
function validateText($text, $config) {
    $errors = [];
    if (empty($text)) {
        $errors[] = 'Texto não pode estar vazio.';
    }
    if (strlen($text) < $config['minTextLength']) {
        $errors[] = 'Texto deve ter pelo menos ' . $config['minTextLength'] . ' caracteres.';
    }
    // Para textos muito grandes, não rejeitar, mas avisar sobre otimização
    if (strlen($text) > $config['maxTextLength']) {
        // Não adicionar erro, apenas avisar que será otimizado
        // A otimização será feita na função compareTexts
    }
    // Verificar caracteres permitidos - mais permissivo para textos extraídos de PDFs
    // Para textos extraídos de PDFs, ser mais tolerante com caracteres especiais
    // Verificar se o texto contém pelo menos alguns caracteres válidos
    if (preg_match('/[a-zA-ZáàâãéèêíìîóòôõúùûçñÁÀÂÃÉÈÊÍÌÎÓÒÔÕÚÙÛÇÑ0-9]/', $text)) {
        // Texto contém caracteres válidos, permitir
        // Não aplicar validação rigorosa para textos de PDFs
    } else {
        $errors[] = 'Texto contém apenas caracteres não permitidos.';
    }
    return $errors;
}
// Determinar se é upload de arquivo ou texto digitado
$isFileUpload = isset($_FILES['file1']) && isset($_FILES['file2']) && 
                $_FILES['file1']['error'] === UPLOAD_ERR_OK && 
                $_FILES['file2']['error'] === UPLOAD_ERR_OK;
if ($isFileUpload) {
    // Processar uploads de arquivos
    $file1Result = processFileUpload($_FILES['file1']);
    $file2Result = processFileUpload($_FILES['file2']);
    if (!$file1Result['success'] || !$file2Result['success']) {
        $error = 'Erro no processamento dos arquivos:<br>';
        if (!$file1Result['success']) {
            $error .= 'Arquivo 1: ' . $file1Result['error'] . '<br>';
        }
        if (!$file2Result['success']) {
            $error .= 'Arquivo 2: ' . $file2Result['error'] . '<br>';
        }
        include 'index.php';
        exit;
    }
    $text1 = $file1Result['content'];
    $text2 = $file2Result['content'];
    $fileInfo = [
        'file1' => $file1Result['file_info'],
        'file2' => $file2Result['file_info']
    ];
} else {
    // Processar textos digitados
    if (!isset($_POST['text1']) || !isset($_POST['text2'])) {
        $error = 'Ambos os textos são obrigatórios.';
        include 'index.php';
        exit;
    }
    $text1 = sanitizeText($_POST['text1']);
    $text2 = sanitizeText($_POST['text2']);
    $fileInfo = null;
}
// Validar textos
$errors1 = validateText($text1, $config);
$errors2 = validateText($text2, $config);
if (!empty($errors1) || !empty($errors2)) {
    $error = 'Erros de validação:<br>';
    if (!empty($errors1)) {
        $error .= 'Texto 1: ' . implode(', ', $errors1) . '<br>';
    }
    if (!empty($errors2)) {
        $error .= 'Texto 2: ' . implode(', ', $errors2) . '<br>';
    }
    include 'index.php';
    exit;
}
// Configurar opções de processamento
$processingOptions = [
    'minLength' => 3,
    'removeNumbers' => isset($_POST['removeNumbers']) ? (bool)$_POST['removeNumbers'] : false,
    'tfNormalization' => $_POST['tfNormalization'] ?? 'raw',
    'idfFormula' => $_POST['idfFormula'] ?? 'smooth',
    'normalizeTfIdf' => isset($_POST['normalizeTfIdf']) ? (bool)$_POST['normalizeTfIdf'] : false,
    'sparseOptimization' => true,
    'topTermsLimit' => 15
];
// Iniciar monitoramento de performance
$performance->startOperation('text_comparison');
$startTime = microtime(true);

// Verificar cache primeiro
$cacheKey = md5($text1 . $text2 . serialize($processingOptions));
$cachedResult = $cache->getCache($text1 . $text2, $processingOptions);

if ($cachedResult) {
    $logger->log('INFO', 'Resultado obtido do cache', ['cache_key' => $cacheKey]);
    $formattedResult = $cachedResult;
    // Adicionar tempo de processamento mesmo para resultados do cache
    $formattedResult['processingTime'] = round((microtime(true) - $startTime) * 1000, 2);
} else {
    // Processar a comparação
    try {
        $result = compareTexts($text1, $text2, $processingOptions);
        $formattedResult = formatSimilarityResult($result);
        
        // Salvar no cache
        $cache->setCache($text1 . $text2, $processingOptions, $formattedResult);
        
        // Adicionar tempo de processamento
        $formattedResult['processingTime'] = round((microtime(true) - $startTime) * 1000, 2);
        
        // Log da análise
        $logger->logTextAnalysis(strlen($text1) + strlen($text2), 'comparison', $formattedResult, $processingOptions);
        
        // Detecção de IA se solicitada
        $aiDetection = null;
        if (isset($_POST['detectAI']) && $_POST['detectAI']) {
            $aiDetection = [
                'text1' => detectAIGeneratedText($text1),
                'text2' => detectAIGeneratedText($text2)
            ];
        }
        // Adicionar informações de arquivo se aplicável
        if ($fileInfo) {
            $formattedResult['fileInfo'] = $fileInfo;
        }
        // Adicionar detecção de IA
        if ($aiDetection) {
            $formattedResult['aiDetection'] = $aiDetection;
        }
        
        // Finalizar monitoramento de performance
        $performance->endOperation('text_comparison');
        
    } catch (Exception $e) {
    $logger->logError('Erro durante processamento de comparação', $e, [
        'text1_length' => strlen($text1),
        'text2_length' => strlen($text2),
        'options' => $processingOptions
    ]);
    $error = 'Erro durante o processamento: ' . $e->getMessage();
    include 'index.php';
    exit;
    }
} // Fim do else do cache
// Garantir que processingTime sempre existe
if (!isset($formattedResult['processingTime'])) {
    $formattedResult['processingTime'] = 0;
}

// Verificar se o processamento demorou muito
if ($formattedResult['processingTime'] > $config['maxProcessingTime'] * 1000) {
    $formattedResult['warning'] = 'Processamento demorou mais que o esperado.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado da Comparação - Sistema de Comparação de Textos</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Sistema de Comparação de Conteúdo de Textos</h1>
            <p>Resultado da análise de similaridade</p>
        </header>
        <main class="main-content">
            <div class="result-container">
                <div class="result-header">
                    <h2>Resultado da Comparação</h2>
                    <a href="index.php" class="btn-back">← Nova Comparação</a>
                </div>
                <?php if ($formattedResult['message']): ?>
                    <div class="alert alert-info">
                        <p><?php echo htmlspecialchars($formattedResult['message']); ?></p>
                    </div>
                <?php endif; ?>
                <div class="similarity-result">
                    <div class="similarity-main">
                        <div class="similarity-percentage">
                            <span class="percentage"><?php echo $formattedResult['percentage']; ?>%</span>
                            <span class="level"><?php echo $formattedResult['level']; ?></span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $formattedResult['percentage']; ?>%"></div>
                        </div>
                    </div>
                    <div class="similarity-details">
                        <h3>Detalhes da Análise</h3>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <span class="stat-label">Palavras no Texto 1:</span>
                                <span class="stat-value"><?php echo number_format($formattedResult['stats']['tokens1']); ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Palavras no Texto 2:</span>
                                <span class="stat-value"><?php echo number_format($formattedResult['stats']['tokens2']); ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Termos únicos (Texto 1):</span>
                                <span class="stat-value"><?php echo number_format($formattedResult['stats']['uniqueTerms1']); ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Termos únicos (Texto 2):</span>
                                <span class="stat-value"><?php echo number_format($formattedResult['stats']['uniqueTerms2']); ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Termos em comum:</span>
                                <span class="stat-value"><?php echo number_format($formattedResult['stats']['commonTerms']); ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Total de termos únicos:</span>
                                <span class="stat-value"><?php echo number_format($formattedResult['stats']['totalUniqueTerms']); ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Tempo de processamento:</span>
                                <span class="stat-value"><?php echo $formattedResult['processingTime']; ?>ms</span>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($formattedResult['metrics'])): ?>
                    <div class="metrics-section">
                        <h3>Métricas Adicionais</h3>
                        <div class="metrics-grid">
                            <div class="metric-item">
                                <span class="metric-label">Similaridade Jaccard:</span>
                                <span class="metric-value"><?php echo round($formattedResult['metrics']['jaccard_similarity'] * 100, 2); ?>%</span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Coeficiente Dice:</span>
                                <span class="metric-value"><?php echo round($formattedResult['metrics']['dice_coefficient'] * 100, 2); ?>%</span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Coeficiente Overlap:</span>
                                <span class="metric-value"><?php echo round($formattedResult['metrics']['overlap_coefficient'] * 100, 2); ?>%</span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Taxa de Termos Únicos:</span>
                                <span class="metric-value"><?php echo round($formattedResult['metrics']['unique_terms_ratio'] * 100, 2); ?>%</span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($formattedResult['aiDetection'])): ?>
                    <div class="ai-detection-section">
                        <h3>🤖 Análise de Detecção de IA</h3>
                        <div class="ai-detection-grid">
                            <div class="ai-detection-item">
                                <h4>Texto 1</h4>
                                <div class="ai-result">
                                    <span class="ai-confidence">
                                        <?php echo round($formattedResult['aiDetection']['text1']['confidence'] * 100, 1); ?>%
                                    </span>
                                    <span class="ai-status <?php echo $formattedResult['aiDetection']['text1']['is_ai_generated'] ? 'ai-detected' : 'human-written'; ?>">
                                        <?php echo $formattedResult['aiDetection']['text1']['is_ai_generated'] ? '🤖 Gerado por IA' : '👤 Escrito por Humano'; ?>
                                    </span>
                                </div>
                                <p class="ai-explanation"><?php echo $formattedResult['aiDetection']['text1']['explanation']; ?></p>
                            </div>
                            <div class="ai-detection-item">
                                <h4>Texto 2</h4>
                                <div class="ai-result">
                                    <span class="ai-confidence">
                                        <?php echo round($formattedResult['aiDetection']['text2']['confidence'] * 100, 1); ?>%
                                    </span>
                                    <span class="ai-status <?php echo $formattedResult['aiDetection']['text2']['is_ai_generated'] ? 'ai-detected' : 'human-written'; ?>">
                                        <?php echo $formattedResult['aiDetection']['text2']['is_ai_generated'] ? '🤖 Gerado por IA' : '👤 Escrito por Humano'; ?>
                                    </span>
                                </div>
                                <p class="ai-explanation"><?php echo $formattedResult['aiDetection']['text2']['explanation']; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($formattedResult['fileInfo'])): ?>
                    <div class="file-info-section">
                        <h3>📁 Informações dos Arquivos</h3>
                        <div class="file-info-grid">
                            <div class="file-info-item">
                                <h4>Arquivo 1</h4>
                                <p><strong>Nome:</strong> <?php echo htmlspecialchars($formattedResult['fileInfo']['file1']['name']); ?></p>
                                <p><strong>Tamanho:</strong> <?php echo formatBytes($formattedResult['fileInfo']['file1']['size']); ?></p>
                                <p><strong>Encoding:</strong> <?php echo $formattedResult['fileInfo']['file1']['encoding']; ?></p>
                            </div>
                            <div class="file-info-item">
                                <h4>Arquivo 2</h4>
                                <p><strong>Nome:</strong> <?php echo htmlspecialchars($formattedResult['fileInfo']['file2']['name']); ?></p>
                                <p><strong>Tamanho:</strong> <?php echo formatBytes($formattedResult['fileInfo']['file2']['size']); ?></p>
                                <p><strong>Encoding:</strong> <?php echo $formattedResult['fileInfo']['file2']['encoding']; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($formattedResult['topTerms'])): ?>
                    <div class="top-terms">
                        <h3>Termos que Mais Contribuem para a Similaridade</h3>
                        <div class="terms-list">
                            <?php foreach ($formattedResult['topTerms'] as $termData): ?>
                                <div class="term-item">
                                    <span class="term-text"><?php echo htmlspecialchars($termData['term']); ?></span>
                                    <span class="term-contribution"><?php echo number_format($termData['contribution'], 4); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (isset($formattedResult['optimization']) && $formattedResult['optimization']['was_optimized']): ?>
                    <div class="optimization-info">
                        <h3>⚡ Otimização de Performance</h3>
                        <div class="optimization-content">
                            <p><strong>Texto otimizado para melhor performance:</strong></p>
                            <ul>
                                <li>Texto 1: <?php echo number_format($formattedResult['optimization']['original_length1']); ?> → <?php echo number_format($formattedResult['optimization']['optimized_length1']); ?> caracteres</li>
                                <li>Texto 2: <?php echo number_format($formattedResult['optimization']['original_length2']); ?> → <?php echo number_format($formattedResult['optimization']['optimized_length2']); ?> caracteres</li>
                            </ul>
                            <p class="optimization-message"><?php echo htmlspecialchars($formattedResult['optimization']['message']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="interpretation">
                    <h3>Interpretação do Resultado</h3>
                    <div class="interpretation-content">
                        <?php
                        $percentage = $formattedResult['percentage'];
                        if ($percentage >= 80) {
                            echo '<p class="interpretation-high">Os textos são <strong>muito similares</strong>. Provavelmente tratam do mesmo assunto ou têm conteúdo muito parecido.</p>';
                        } elseif ($percentage >= 60) {
                            echo '<p class="interpretation-medium">Os textos têm <strong>similaridade alta</strong>. Compartilham muitos conceitos e vocabulário.</p>';
                        } elseif ($percentage >= 40) {
                            echo '<p class="interpretation-medium">Os textos têm <strong>similaridade média</strong>. Alguns conceitos são compartilhados, mas há diferenças significativas.</p>';
                        } elseif ($percentage >= 20) {
                            echo '<p class="interpretation-low">Os textos têm <strong>similaridade baixa</strong>. Poucos conceitos são compartilhados.</p>';
                        } else {
                            echo '<p class="interpretation-low">Os textos têm <strong>similaridade muito baixa</strong>. Praticamente não há relação entre eles.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </main>
        <footer class="footer">
            <p>&copy; 2024 Sistema de Comparação de Textos - Desenvolvido para TCC</p>
        </footer>
    </div>
</body>
</html>
