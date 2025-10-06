<?php
/**
 * Sistema de Análise Avançada de Textos
 * Processamento completo com verificação de plágio, detecção de IA e validação de referências
 * 
 * @version 1.0
 * @author Sistema TCC
 */
// Incluir arquivos de funções
require_once 'functions/similarity.php';
require_once 'functions/file_processor.php';
require_once 'functions/advanced_analysis.php';
// Configurações do sistema
$config = [
    'maxTextLength' => 1000000, // Aumentado para artigos muito longos
    'minTextLength' => 50,
    'maxProcessingTime' => 1200, // 20 minutos para análise avançada
    'allowedChars' => '/^[\p{L}\p{N}\p{P}\p{S}\p{Z}\p{M}]*$/u', // Caracteres permitidos (Unicode) - incluindo marcas diacríticas
    'memoryLimit' => '2G',
    'executionTimeLimit' => 1200
];
// Configurar limites de execução
set_time_limit($config['executionTimeLimit']);
ini_set('memory_limit', $config['memoryLimit']);
// Verificar se o método é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: advanced_analysis.php');
    exit;
}
// Função para sanitizar texto
function sanitizeText($text) {
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
        // A otimização será feita na função performAdvancedAnalysis
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
// Determinar tipo de entrada
$inputType = '';
$text = '';
$fileInfo = null;
if (isset($_FILES['analysisFile']) && $_FILES['analysisFile']['error'] === UPLOAD_ERR_OK) {
    // Upload de arquivo
    $inputType = 'file';
    $fileResult = processFileUpload($_FILES['analysisFile']);
    if (!$fileResult['success']) {
        $error = 'Erro no processamento do arquivo: ' . $fileResult['error'];
        include 'advanced_analysis.php';
        exit;
    }
    $text = $fileResult['content'];
    $fileInfo = $fileResult['file_info'];
} elseif (isset($_POST['analysisText']) && !empty(trim($_POST['analysisText']))) {
    // Texto digitado
    $inputType = 'text';
    $text = sanitizeText($_POST['analysisText']);
} elseif (isset($_POST['analysisUrl']) && !empty(trim($_POST['analysisUrl']))) {
    // URL/Website
    $inputType = 'url';
    $url = filter_var($_POST['analysisUrl'], FILTER_VALIDATE_URL);
    if (!$url) {
        $error = 'URL inválida fornecida.';
        include 'advanced_analysis.php';
        exit;
    }
    // Simular extração de texto de URL (em implementação real, usar cURL)
    $text = "Texto extraído de: " . $url . "\n\n[Simulação: Em implementação real, o texto seria extraído do website usando técnicas de web scraping.]";
} else {
    $error = 'Nenhuma entrada válida fornecida. Verifique se selecionou um arquivo, digitou um texto ou forneceu uma URL.';
    include 'advanced_analysis.php';
    exit;
}
// Validar texto
$errors = validateText($text, $config);
if (!empty($errors)) {
    $error = 'Erros de validação: ' . implode(', ', $errors);
    include 'advanced_analysis.php';
    exit;
}
// Configurar opções de análise
$analysisOptions = [
    'plagiarismCheck' => isset($_POST['plagiarismCheck']),
    'aiDetection' => isset($_POST['aiDetection']),
    'referenceValidation' => isset($_POST['referenceValidation']),
    'sentimentAnalysis' => isset($_POST['sentimentAnalysis']),
    'readabilityAnalysis' => isset($_POST['readabilityAnalysis']),
    'keywordAnalysis' => isset($_POST['keywordAnalysis']),
    'searchDepth' => $_POST['searchDepth'] ?? 'standard',
    'language' => $_POST['language'] ?? 'pt',
    'similarityThreshold' => floatval($_POST['similarityThreshold'] ?? 0.7)
];
// Iniciar cronômetro
$startTime = microtime(true);
// Realizar análise avançada
try {
    // Verificar se a função existe
    if (!function_exists('performAdvancedAnalysis')) {
        throw new Exception('Função performAdvancedAnalysis não encontrada. Verifique se o arquivo functions/advanced_analysis.php está incluído corretamente.');
    }
    $analysisResult = performAdvancedAnalysis($text, $analysisOptions);
    // Adicionar tempo de processamento
    $analysisResult['processingTime'] = round((microtime(true) - $startTime) * 1000, 2);
    // Adicionar informações de arquivo se aplicável
    if ($fileInfo) {
        $analysisResult['fileInfo'] = $fileInfo;
    }
    // Adicionar tipo de entrada
    $analysisResult['inputType'] = $inputType;
} catch (Exception $e) {
    $error = 'Erro durante a análise: ' . $e->getMessage();
    error_log("Erro na análise avançada: " . $e->getMessage());
    include 'advanced_analysis.php';
    exit;
}
// Verificar se o processamento demorou muito
if ($analysisResult['processingTime'] > $config['maxProcessingTime'] * 1000) {
    $analysisResult['warning'] = 'Análise demorou mais que o esperado.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado da Análise Avançada - Sistema de Comparação</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/advanced.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>🔍 Resultado da Análise Avançada</h1>
            <p>Análise completa realizada com sucesso</p>
        </header>
        <main class="main-content">
            <div class="result-container">
                <div class="result-header">
                    <h2>📊 Resumo da Análise</h2>
                    <div class="result-actions">
                        <a href="advanced_analysis.php" class="btn-back">← Nova Análise</a>
                        <button onclick="window.print()" class="btn-print">🖨️ Imprimir</button>
                    </div>
                </div>
                <!-- Informações Gerais -->
                <div class="analysis-overview">
                    <div class="overview-grid">
                        <div class="overview-item">
                            <span class="overview-label">Tipo de Entrada:</span>
                            <span class="overview-value">
                                <?php 
                                switch($analysisResult['inputType']) {
                                    case 'file': echo '📁 Arquivo'; break;
                                    case 'text': echo '✍️ Texto'; break;
                                    case 'url': echo '🌐 URL'; break;
                                }
                                ?>
                            </span>
                        </div>
                        <div class="overview-item">
                            <span class="overview-label">Tamanho do Texto:</span>
                            <span class="overview-value"><?php echo number_format($analysisResult['text_length']); ?> caracteres</span>
                        </div>
                        <div class="overview-item">
                            <span class="overview-label">Palavras:</span>
                            <span class="overview-value"><?php echo number_format($analysisResult['word_count']); ?></span>
                        </div>
                        <div class="overview-item">
                            <span class="overview-label">Tempo de Processamento:</span>
                            <span class="overview-value"><?php echo $analysisResult['processingTime']; ?>ms</span>
                        </div>
                    </div>
                </div>
                <!-- Verificação de Plágio -->
                <?php if (isset($analysisResult['plagiarism'])): ?>
                <div class="analysis-section plagiarism-section">
                    <h3>🔍 Verificação de Plágio</h3>
                    <div class="plagiarism-result">
                        <div class="plagiarism-score">
                            <span class="score-value"><?php echo round($analysisResult['plagiarism']['plagiarism_score'], 1); ?>%</span>
                            <span class="score-label">Score de Plágio</span>
                        </div>
                        <div class="plagiarism-status">
                            <?php if ($analysisResult['plagiarism']['plagiarism_detected']): ?>
                                <span class="status-badge danger">⚠️ Plágio Detectado</span>
                            <?php else: ?>
                                <span class="status-badge success">✅ Original</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="plagiarism-details">
                        <p><strong>Correspondências encontradas:</strong> <?php echo $analysisResult['plagiarism']['matches_found']; ?></p>
                        <p><strong>Análise:</strong> <?php echo $analysisResult['plagiarism']['analysis_summary']; ?></p>
                    </div>
                    <?php if (!empty($analysisResult['plagiarism']['matches'])): ?>
                    <div class="matches-list">
                        <h4>Correspondências Encontradas:</h4>
                        <?php foreach (array_slice($analysisResult['plagiarism']['matches'], 0, 5) as $match): ?>
                        <div class="match-item">
                            <div class="match-content">
                                <p><strong>Texto Original:</strong> <?php echo htmlspecialchars(substr($match['original_sentence'], 0, 100)) . '...'; ?></p>
                                <p><strong>Correspondência:</strong> <?php echo htmlspecialchars(substr($match['matched_content'], 0, 100)) . '...'; ?></p>
                                <p><strong>Similaridade:</strong> <?php echo round($match['similarity'] * 100, 1); ?>% (<?php echo $match['match_type']; ?>)</p>
                                <p><strong>Fonte:</strong> <a href="<?php echo htmlspecialchars($match['source_url']); ?>" target="_blank"><?php echo htmlspecialchars($match['source_title']); ?></a></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <!-- Detecção de IA -->
                <?php if (isset($analysisResult['ai_detection'])): ?>
                <div class="analysis-section ai-section">
                    <h3>🤖 Detecção de Inteligência Artificial</h3>
                    <div class="ai-result">
                        <div class="ai-confidence">
                            <span class="confidence-value"><?php echo round($analysisResult['ai_detection']['confidence'] * 100, 1); ?>%</span>
                            <span class="confidence-label">Confiança</span>
                        </div>
                        <div class="ai-status">
                            <?php if ($analysisResult['ai_detection']['is_ai_generated']): ?>
                                <span class="status-badge warning">🤖 Gerado por IA</span>
                            <?php else: ?>
                                <span class="status-badge success">👤 Escrito por Humano</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="ai-explanation">
                        <p><?php echo $analysisResult['ai_detection']['explanation']; ?></p>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Validação de Referências -->
                <?php if (isset($analysisResult['reference_validation'])): ?>
                <div class="analysis-section reference-section">
                    <h3>📚 Validação de Referências</h3>
                    <div class="reference-stats">
                        <div class="stat-item">
                            <span class="stat-label">Total de Referências:</span>
                            <span class="stat-value"><?php echo $analysisResult['reference_validation']['total_references']; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Válidas:</span>
                            <span class="stat-value success"><?php echo $analysisResult['reference_validation']['valid_references']; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Inválidas:</span>
                            <span class="stat-value danger"><?php echo $analysisResult['reference_validation']['invalid_references']; ?></span>
                        </div>
                    </div>
                    <div class="reference-summary">
                        <p><?php echo $analysisResult['reference_validation']['summary']; ?></p>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Análise de Sentimento -->
                <?php if (isset($analysisResult['sentiment'])): ?>
                <div class="analysis-section sentiment-section">
                    <h3>😊 Análise de Sentimento</h3>
                    <div class="sentiment-result">
                        <div class="sentiment-score">
                            <span class="sentiment-value"><?php echo ucfirst($analysisResult['sentiment']['sentiment']); ?></span>
                            <span class="sentiment-confidence"><?php echo round($analysisResult['sentiment']['confidence'] * 100, 1); ?>% confiança</span>
                        </div>
                        <div class="sentiment-stats">
                            <div class="sentiment-stat">
                                <span class="stat-label">Palavras Positivas:</span>
                                <span class="stat-value"><?php echo $analysisResult['sentiment']['positive_words']; ?></span>
                            </div>
                            <div class="sentiment-stat">
                                <span class="stat-label">Palavras Negativas:</span>
                                <span class="stat-value"><?php echo $analysisResult['sentiment']['negative_words']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="sentiment-analysis">
                        <p><?php echo $analysisResult['sentiment']['analysis']; ?></p>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Análise de Legibilidade -->
                <?php if (isset($analysisResult['readability'])): ?>
                <div class="analysis-section readability-section">
                    <h3>📖 Análise de Legibilidade</h3>
                    <div class="readability-result">
                        <div class="readability-score">
                            <span class="score-value"><?php echo $analysisResult['readability']['readability_score']; ?></span>
                            <span class="score-label">Score de Legibilidade</span>
                        </div>
                        <div class="readability-level">
                            <span class="level-badge"><?php echo $analysisResult['readability']['difficulty_level']; ?></span>
                        </div>
                    </div>
                    <div class="readability-stats">
                        <div class="readability-stat">
                            <span class="stat-label">Média de Palavras por Frase:</span>
                            <span class="stat-value"><?php echo $analysisResult['readability']['avg_words_per_sentence']; ?></span>
                        </div>
                        <div class="readability-stat">
                            <span class="stat-label">Média de Sílabas por Palavra:</span>
                            <span class="stat-value"><?php echo $analysisResult['readability']['avg_syllables_per_word']; ?></span>
                        </div>
                    </div>
                    <div class="readability-analysis">
                        <p><?php echo $analysisResult['readability']['analysis']; ?></p>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Análise de Palavras-chave -->
                <?php if (isset($analysisResult['keywords'])): ?>
                <div class="analysis-section keywords-section">
                    <h3>🔑 Palavras-chave Identificadas</h3>
                    <div class="keywords-list">
                        <?php foreach (array_slice($analysisResult['keywords'], 0, 15) as $keyword): ?>
                        <div class="keyword-item">
                            <span class="keyword-word"><?php echo htmlspecialchars($keyword['word']); ?></span>
                            <span class="keyword-score"><?php echo round($keyword['tfidf'], 3); ?></span>
                            <span class="keyword-freq"><?php echo $keyword['frequency']; ?>x</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Resumo Geral -->
                <div class="analysis-section summary-section">
                    <h3>📋 Resumo Geral da Análise</h3>
                    <div class="summary-content">
                        <pre><?php echo htmlspecialchars($analysisResult['summary']); ?></pre>
                    </div>
                </div>
                <!-- Informações do Arquivo -->
                <?php if (isset($analysisResult['fileInfo'])): ?>
                <div class="analysis-section file-info-section">
                    <h3>📁 Informações do Arquivo</h3>
                    <div class="file-info-grid">
                        <div class="file-info-item">
                            <span class="info-label">Nome:</span>
                            <span class="info-value"><?php echo htmlspecialchars($analysisResult['fileInfo']['name']); ?></span>
                        </div>
                        <div class="file-info-item">
                            <span class="info-label">Tamanho:</span>
                            <span class="info-value"><?php echo formatBytes($analysisResult['fileInfo']['size']); ?></span>
                        </div>
                        <div class="file-info-item">
                            <span class="info-label">Encoding:</span>
                            <span class="info-value"><?php echo $analysisResult['fileInfo']['encoding']; ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Otimização de Performance -->
                <?php if (isset($analysisResult['was_optimized']) && $analysisResult['was_optimized']): ?>
                <div class="analysis-section optimization-section">
                    <h3>⚡ Otimização de Performance</h3>
                    <div class="optimization-content">
                        <p><strong>Texto otimizado para melhor performance:</strong></p>
                        <ul>
                            <li>Tamanho original: <?php echo number_format($analysisResult['original_text_length']); ?> caracteres</li>
                            <li>Tamanho otimizado: <?php echo number_format($analysisResult['text_length']); ?> caracteres</li>
                            <li>Redução: <?php echo round((1 - $analysisResult['text_length'] / $analysisResult['original_text_length']) * 100, 1); ?>%</li>
                        </ul>
                        <p class="optimization-message">Análise baseada em amostragem inteligente para melhor performance.</p>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Avisos -->
                <?php if (isset($analysisResult['warning'])): ?>
                <div class="analysis-section warning-section">
                    <h3>⚠️ Avisos</h3>
                    <div class="warning-content">
                        <p><?php echo htmlspecialchars($analysisResult['warning']); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
        <footer class="footer">
            <p>&copy; 2024 Sistema de Análise Avançada de Textos - Desenvolvido para TCC</p>
        </footer>
    </div>
    <script>
        // Função para imprimir
        function printAnalysis() {
            window.print();
        }
        // Função para copiar resumo
        function copySummary() {
            const summary = document.querySelector('.summary-content pre').textContent;
            navigator.clipboard.writeText(summary).then(() => {
                alert('Resumo copiado para a área de transferência!');
            });
        }
        // Adicionar botão de copiar resumo
        document.addEventListener('DOMContentLoaded', function() {
            const summarySection = document.querySelector('.summary-section');
            if (summarySection) {
                const copyButton = document.createElement('button');
                copyButton.className = 'btn-copy';
                copyButton.innerHTML = '📋 Copiar Resumo';
                copyButton.onclick = copySummary;
                summarySection.querySelector('h3').appendChild(copyButton);
            }
        });
    </script>
</body>
</html>
