<?php
/**
 * Sistema de Comparação de Conteúdo de Textos
 * Processamento de arquivos de texto
 * 
 * @version 2.0
 * @author Sistema TCC
 */
/**
 * Processa upload de arquivos de texto
 * 
 * @param array $file Array $_FILES do arquivo
 * @param int $maxSize Tamanho máximo em bytes (padrão: 5MB)
 * @return array Resultado do processamento
 */
function processFileUpload($file, $maxSize = 5242880) { // 5MB
    $result = [
        'success' => false,
        'content' => '',
        'error' => '',
        'file_info' => []
    ];
    // Verificar se arquivo foi enviado
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        $result['error'] = 'Erro no upload do arquivo.';
        return $result;
    }
    // Verificar tamanho
    if ($file['size'] > $maxSize) {
        $result['error'] = 'Arquivo muito grande. Máximo: ' . formatBytes($maxSize);
        return $result;
    }
    // Verificar tipo de arquivo - Aceitar arquivos de texto e documentos
    $allowedTypes = [
        'text/plain', 
        'application/octet-stream',
        'text/html',
        'text/css',
        'text/javascript',
        'application/json',
        'application/xml',
        'text/xml',
        'text/csv',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation'
    ];
    $allowedExtensions = [
        'txt', 'text', 'md', 'html', 'htm', 'css', 'js', 'json', 'xml', 'csv', 'log',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'
    ];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileType = $file['type'];
    // Verificar se é um arquivo aceito baseado na extensão ou tipo MIME
    $isAllowedFile = in_array($fileExtension, $allowedExtensions) || 
                     in_array($fileType, $allowedTypes) ||
                     strpos($fileType, 'text/') === 0 ||
                     strpos($fileType, 'application/') === 0;
    if (!$isAllowedFile) {
        $result['error'] = 'Tipo de arquivo não permitido. Formatos aceitos: .txt, .md, .html, .css, .js, .json, .xml, .csv, .log, .pdf, .doc, .docx, .xls, .xlsx, .ppt, .pptx.';
        return $result;
    }
    // Processar conteúdo baseado no tipo de arquivo
    $content = '';
    switch ($fileExtension) {
        case 'pdf':
            $content = extractTextFromPDF($file['tmp_name']);
            break;
        case 'doc':
        case 'docx':
            $content = extractTextFromWord($file['tmp_name']);
            break;
        case 'xls':
        case 'xlsx':
            $content = extractTextFromExcel($file['tmp_name']);
            break;
        case 'ppt':
        case 'pptx':
            $content = extractTextFromPowerPoint($file['tmp_name']);
            break;
        default:
            // Para arquivos de texto simples
            $content = file_get_contents($file['tmp_name']);
            break;
    }
    if ($content === false || empty($content)) {
        $result['error'] = 'Erro ao extrair texto do arquivo. Verifique se o arquivo não está corrompido.';
        return $result;
    }
    // Verificar encoding e converter se necessário
    $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
    if ($encoding && $encoding !== 'UTF-8') {
        $content = mb_convert_encoding($content, 'UTF-8', $encoding);
    }
    // Verificar se o conteúdo é texto válido
    if (!isValidTextContent($content)) {
        $result['error'] = 'Arquivo não contém texto válido.';
        return $result;
    }
    $result['success'] = true;
    $result['content'] = $content;
    $result['file_info'] = [
        'name' => $file['name'],
        'size' => $file['size'],
        'type' => $file['type'],
        'encoding' => $encoding ?: 'UTF-8'
    ];
    return $result;
}
/**
 * Verifica se o conteúdo é texto válido
 * 
 * @param string $content Conteúdo a ser verificado
 * @return bool True se for texto válido
 */
function isValidTextContent($content) {
    // Verificar se contém caracteres de controle excessivos
    $controlChars = preg_match_all('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $content);
    $totalChars = strlen($content);
    if ($totalChars > 0 && $controlChars / $totalChars > 0.1) {
        return false;
    }
    // Verificar se tem pelo menos alguns caracteres imprimíveis
    $printableChars = preg_match_all('/[\x20-\x7E\xC0-\xFF]/', $content);
    return $printableChars > 10; // Pelo menos 10 caracteres imprimíveis
}
/**
 * Formata tamanho de arquivo em bytes para formato legível
 * 
 * @param int $bytes Tamanho em bytes
 * @return string Tamanho formatado
 */
function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}
/**
 * Extrai metadados do arquivo de texto
 * 
 * @param string $content Conteúdo do arquivo
 * @return array Metadados extraídos
 */
function extractFileMetadata($content) {
    $lines = explode("\n", $content);
    $words = preg_split('/\s+/', $content);
    $sentences = preg_split('/[.!?]+/', $content);
    return [
        'char_count' => strlen($content),
        'word_count' => count($words),
        'line_count' => count($lines),
        'sentence_count' => count($sentences),
        'avg_words_per_sentence' => count($words) / max(count($sentences), 1),
        'avg_chars_per_word' => strlen($content) / max(count($words), 1),
        'has_paragraphs' => strpos($content, "\n\n") !== false,
        'encoding' => mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1'], true) ?: 'Unknown'
    ];
}
/**
 * Processa múltiplos arquivos
 * 
 * @param array $files Array de arquivos
 * @return array Resultados do processamento
 */
function processMultipleFiles($files) {
    $results = [];
    foreach ($files as $key => $file) {
        if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
            $result = processFileUpload($file);
            $results[$key] = $result;
        }
    }
    return $results;
}
/**
 * Valida arquivo antes do processamento
 * 
 * @param array $file Array $_FILES
 * @return array Resultado da validação
 */
function validateFile($file) {
    $errors = [];
    if (!isset($file)) {
        $errors[] = 'Nenhum arquivo foi enviado.';
        return ['valid' => false, 'errors' => $errors];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = 'Arquivo muito grande.';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errors[] = 'Upload incompleto.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errors[] = 'Nenhum arquivo foi enviado.';
                break;
            default:
                $errors[] = 'Erro desconhecido no upload.';
        }
        return ['valid' => false, 'errors' => $errors];
    }
    // Verificar extensão
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ['txt'])) {
        $errors[] = 'Apenas arquivos .txt são permitidos.';
    }
    // Verificar tamanho (5MB)
    if ($file['size'] > 5242880) {
        $errors[] = 'Arquivo muito grande. Máximo: 5MB.';
    }
    // Verificar se é realmente um arquivo de texto
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mimeType, ['text/plain', 'application/octet-stream'])) {
        $errors[] = 'Arquivo não é um texto válido.';
    }
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}
/**
 * Gera nome único para arquivo temporário
 * 
 * @param string $originalName Nome original do arquivo
 * @return string Nome único
 */
function generateUniqueFileName($originalName) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $timestamp = time();
    $random = mt_rand(1000, 9999);
    return "upload_{$timestamp}_{$random}.{$extension}";
}
/**
 * Limpa arquivos temporários antigos
 * 
 * @param string $tempDir Diretório temporário
 * @param int $maxAge Idade máxima em segundos (padrão: 1 hora)
 */
function cleanupTempFiles($tempDir, $maxAge = 3600) {
    if (!is_dir($tempDir)) {
        return;
    }
    $files = glob($tempDir . '/upload_*');
    $now = time();
    foreach ($files as $file) {
        if (is_file($file) && ($now - filemtime($file)) > $maxAge) {
            unlink($file);
        }
    }
}
/**
 * Extrai texto de arquivo PDF
 * 
 * @param string $filePath Caminho do arquivo PDF
 * @return string Texto extraído
 */
function extractTextFromPDF($filePath) {
    // Usar biblioteca PDFParser para extração robusta
    try {
        // Verificar se o autoloader do Composer está disponível
        if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
            require_once __DIR__ . '/../vendor/autoload.php';
            // Usar PDFParser para extração profissional
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();
            // Limpar e normalizar texto extraído
            $text = preg_replace('/\s+/', ' ', $text);
            $text = trim($text);
            // Filtrar metadados e caracteres especiais
            $lines = explode("\n", $text);
            $cleanLines = [];
            foreach ($lines as $line) {
                $line = trim($line);
                // Filtrar linhas que são claramente metadados
                if (strlen($line) > 3 && 
                    !preg_match('/^(Adobe|Identity|pt-BR|R\s|obj|endobj|stream|endstream|Type|Page|MediaBox|Resources|Font|ExtGState|ProcSet|Contents|Group|StructParents|ViewerPreferences|Metadata|MarkInfo|StructTreeRoot|Lang|Count|Kids|en-US)/', $line) &&
                    !preg_match('/^[0-9\s\.\-]+$/', $line)) {
                    $cleanLines[] = $line;
                }
            }
            $text = implode(' ', $cleanLines);
            // Limpar espaços extras
            $text = preg_replace('/\s+/', ' ', $text);
            $text = trim($text);
            return $text ?: '[Texto não pôde ser extraído do PDF com PDFParser.]';
        } else {
            // Fallback para método nativo se PDFParser não estiver disponível
            return extractTextFromPDFNative($filePath);
        }
    } catch (Exception $e) {
        // Se PDFParser falhar, usar método nativo como fallback
        return extractTextFromPDFNative($filePath);
    }
}
/**
 * Método nativo de extração de PDF (fallback)
 * 
 * @param string $filePath Caminho do arquivo PDF
 * @return string Texto extraído
 */
function extractTextFromPDFNative($filePath) {
    $content = file_get_contents($filePath);
    $text = '';
    if ($content) {
        // Estratégia 1: Extrair texto de streams decodificados
        preg_match_all('/stream\s*(.*?)\s*endstream/s', $content, $streamMatches);
        foreach ($streamMatches[1] as $stream) {
            // Tentar decodificar stream (método básico)
            $decoded = @gzuncompress($stream);
            if ($decoded === false) {
                $decoded = $stream;
            }
            // Extrair texto entre parênteses no stream
            preg_match_all('/\((.*?)\)/', $decoded, $textMatches);
            foreach ($textMatches[1] as $match) {
                if (preg_match('/^[a-zA-ZáàâãéèêíìîóòôõúùûçñÁÀÂÃÉÈÊÍÌÎÓÒÔÕÚÙÛÇÑ\s\d\.,;:!?\-\(\)]+$/', $match) && 
                    strlen($match) > 2 && 
                    !preg_match('/^[0-9\s\.\-]+$/', $match) &&
                    !preg_match('/^(Adobe|Identity|pt-BR|R\s|obj|endobj|stream|endstream)/', $match)) {
                    $text .= $match . ' ';
                }
            }
        }
        // Estratégia 2: Procurar por padrões de texto em operadores PDF
        preg_match_all('/BT\s*(.*?)\s*ET/s', $content, $btMatches);
        foreach ($btMatches[1] as $match) {
            // Extrair texto entre operadores de texto
            preg_match_all('/\((.*?)\)\s*Tj/', $match, $tjMatches);
            foreach ($tjMatches[1] as $tjMatch) {
                if (strlen($tjMatch) > 1 && 
                    preg_match('/^[a-zA-ZáàâãéèêíìîóòôõúùûçñÁÀÂÃÉÈÊÍÌÎÓÒÔÕÚÙÛÇÑ\s\d\.,;:!?\-\(\)]+$/', $tjMatch) &&
                    !preg_match('/^(Adobe|Identity|pt-BR|R\s|obj|endobj)/', $tjMatch)) {
                    $text .= $tjMatch . ' ';
                }
            }
        }
        // Limpar e normalizar texto
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
    }
    return trim($text) ?: '[Texto não pôde ser extraído do PDF. Método nativo limitado.]';
}
/**
 * Extrai texto de arquivo Word (DOC/DOCX)
 * 
 * @param string $filePath Caminho do arquivo Word
 * @return string Texto extraído
 */
function extractTextFromWord($filePath) {
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    if ($extension === 'docx') {
        // DOCX é um arquivo ZIP
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($filePath) === TRUE) {
                $content = $zip->getFromName('word/document.xml');
                $zip->close();
                if ($content) {
                    // Remover tags XML e extrair texto
                    $content = strip_tags($content);
                    $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
                    return trim($content) ?: '[Texto não pôde ser extraído do DOCX.]';
                }
            }
        }
        return '[Extensão ZipArchive não disponível para processar DOCX.]';
    } else {
        // DOC antigo (formato binário) - simulação
        return '[Arquivo DOC antigo detectado. Em implementação real, use bibliotecas especializadas.]';
    }
}
/**
 * Extrai texto de arquivo Excel (XLS/XLSX)
 * 
 * @param string $filePath Caminho do arquivo Excel
 * @return string Texto extraído
 */
function extractTextFromExcel($filePath) {
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    if ($extension === 'xlsx') {
        // XLSX é um arquivo ZIP
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($filePath) === TRUE) {
                $content = $zip->getFromName('xl/sharedStrings.xml');
                $zip->close();
                if ($content) {
                    // Extrair texto das strings compartilhadas
                    preg_match_all('/<t[^>]*>(.*?)<\/t>/', $content, $matches);
                    $text = implode(' ', $matches[1]);
                    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
                    return trim($text) ?: '[Texto não pôde ser extraído do XLSX.]';
                }
            }
        }
        return '[Extensão ZipArchive não disponível para processar XLSX.]';
    } else {
        // XLS antigo (formato binário) - simulação
        return '[Arquivo XLS antigo detectado. Em implementação real, use bibliotecas especializadas como PhpSpreadsheet.]';
    }
}
/**
 * Extrai texto de arquivo PowerPoint (PPT/PPTX)
 * 
 * @param string $filePath Caminho do arquivo PowerPoint
 * @return string Texto extraído
 */
function extractTextFromPowerPoint($filePath) {
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    if ($extension === 'pptx') {
        // PPTX é um arquivo ZIP
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($filePath) === TRUE) {
                $text = '';
                // Extrair texto de todos os slides
                for ($i = 1; $i <= 100; $i++) { // Máximo 100 slides
                    $slideContent = $zip->getFromName("ppt/slides/slide{$i}.xml");
                    if ($slideContent) {
                        $slideContent = strip_tags($slideContent);
                        $slideContent = html_entity_decode($slideContent, ENT_QUOTES, 'UTF-8');
                        $text .= $slideContent . ' ';
                    } else {
                        break; // Não há mais slides
                    }
                }
                $zip->close();
                return trim($text) ?: '[Texto não pôde ser extraído do PPTX.]';
            }
        }
        return '[Extensão ZipArchive não disponível para processar PPTX.]';
    } else {
        // PPT antigo (formato binário) - simulação
        return '[Arquivo PPT antigo detectado. Em implementação real, use bibliotecas especializadas.]';
    }
}
?>
