<?php
/**
 * Sistema de Análise Avançada de Textos
 * Funcionalidades para verificação de plágio, validação de referências e análise online
 * 
 * @version 1.0
 * @author Sistema TCC
 */
/**
 * Verifica plágio online usando APIs de busca
 * 
 * @param string $text Texto para verificar
 * @param array $options Opções de configuração
 * @return array Resultado da verificação de plágio
 */
function checkPlagiarismOnline($text, $options = []) {
    $defaultOptions = [
        'searchDepth' => 'standard',
        'language' => 'pt',
        'similarityThreshold' => 0.7,
        'maxResults' => 10
    ];
    $options = array_merge($defaultOptions, $options);
    // Extrair frases significativas do texto
    $sentences = extractSignificantSentences($text);
    $plagiarismResults = [];
    foreach ($sentences as $sentence) {
        if (strlen(trim($sentence)) < 20) continue; // Ignorar frases muito curtas
        $searchResults = searchTextOnline($sentence, $options);
        if (!empty($searchResults)) {
            foreach ($searchResults as $result) {
                $similarity = calculateTextSimilarity($sentence, $result['content']);
                if ($similarity >= $options['similarityThreshold']) {
                    $plagiarismResults[] = [
                        'original_sentence' => $sentence,
                        'matched_content' => $result['content'],
                        'similarity' => $similarity,
                        'source_url' => $result['url'],
                        'source_title' => $result['title'],
                        'match_type' => classifyMatchType($similarity)
                    ];
                }
            }
        }
    }
    // Calcular score geral de plágio
    $plagiarismScore = calculatePlagiarismScore($plagiarismResults);
    return [
        'plagiarism_detected' => !empty($plagiarismResults),
        'plagiarism_score' => $plagiarismScore,
        'matches_found' => count($plagiarismResults),
        'matches' => $plagiarismResults,
        'analysis_summary' => generatePlagiarismSummary($plagiarismResults, $plagiarismScore)
    ];
}
/**
 * Extrai frases significativas do texto
 * 
 * @param string $text Texto original
 * @return array Array de frases significativas
 */
function extractSignificantSentences($text) {
    // Dividir em frases
    $sentences = preg_split('/[.!?]+/', $text);
    $significantSentences = [];
    foreach ($sentences as $sentence) {
        $sentence = trim($sentence);
        // Filtrar frases muito curtas ou muito longas
        if (strlen($sentence) < 20 || strlen($sentence) > 200) {
            continue;
        }
        // Verificar se a frase contém palavras significativas
        $wordCount = str_word_count($sentence);
        if ($wordCount >= 5) {
            $significantSentences[] = $sentence;
        }
    }
    return $significantSentences;
}
/**
 * Busca texto online usando APIs de busca
 * 
 * @param string $query Texto para buscar
 * @param array $options Opções de busca
 * @return array Resultados da busca
 */
function searchTextOnline($query, $options) {
    $results = [];
    // Simular busca online (em implementação real, usar APIs como Google Custom Search, Bing, etc.)
    $mockResults = [
        [
            'title' => 'Resultado de Busca 1',
            'content' => 'Conteúdo similar encontrado online...',
            'url' => 'https://exemplo.com/artigo1'
        ],
        [
            'title' => 'Resultado de Busca 2', 
            'content' => 'Outro conteúdo relacionado...',
            'url' => 'https://exemplo.com/artigo2'
        ]
    ];
    // Em implementação real, fazer chamadas para APIs de busca
    // $results = callSearchAPI($query, $options);
    return $mockResults;
}
/**
 * Calcula similaridade entre dois textos
 * 
 * @param string $text1 Primeiro texto
 * @param string $text2 Segundo texto
 * @return float Similaridade (0-1)
 */
function calculateTextSimilarity($text1, $text2) {
    // Normalizar textos
    $text1 = strtolower(trim($text1));
    $text2 = strtolower(trim($text2));
    // Calcular similaridade usando algoritmo de Jaccard
    $words1 = array_unique(explode(' ', preg_replace('/[^a-z\s]/', ' ', $text1)));
    $words2 = array_unique(explode(' ', preg_replace('/[^a-z\s]/', ' ', $text2)));
    $intersection = array_intersect($words1, $words2);
    $union = array_unique(array_merge($words1, $words2));
    if (empty($union)) return 0;
    return count($intersection) / count($union);
}
/**
 * Classifica o tipo de correspondência
 * 
 * @param float $similarity Similaridade calculada
 * @return string Tipo de correspondência
 */
function classifyMatchType($similarity) {
    if ($similarity >= 0.9) return 'Exata';
    if ($similarity >= 0.8) return 'Muito Similar';
    if ($similarity >= 0.7) return 'Similar';
    if ($similarity >= 0.5) return 'Parcialmente Similar';
    return 'Pouco Similar';
}
/**
 * Calcula score geral de plágio
 * 
 * @param array $matches Matches encontrados
 * @return float Score de plágio (0-100)
 */
function calculatePlagiarismScore($matches) {
    if (empty($matches)) return 0;
    $totalSimilarity = 0;
    $weightedScore = 0;
    foreach ($matches as $match) {
        $similarity = $match['similarity'];
        $weight = min($similarity * 2, 1); // Peso baseado na similaridade
        $totalSimilarity += $similarity;
        $weightedScore += $similarity * $weight;
    }
    $avgSimilarity = $totalSimilarity / count($matches);
    $matchCount = count($matches);
    // Fórmula para calcular score final
    $score = ($avgSimilarity * 0.7 + $weightedScore * 0.3) * 100;
    return min($score, 100);
}
/**
 * Gera resumo da análise de plágio
 * 
 * @param array $matches Matches encontrados
 * @param float $score Score de plágio
 * @return string Resumo da análise
 */
function generatePlagiarismSummary($matches, $score) {
    if (empty($matches)) {
        return "Nenhum plágio detectado. O texto parece ser original.";
    }
    $matchCount = count($matches);
    $highSimilarityMatches = array_filter($matches, function($match) {
        return $match['similarity'] >= 0.8;
    });
    if ($score >= 80) {
        return "Alto risco de plágio detectado. {$matchCount} correspondências encontradas, sendo " . count($highSimilarityMatches) . " com alta similaridade.";
    } elseif ($score >= 50) {
        return "Risco moderado de plágio. {$matchCount} correspondências encontradas com similaridade moderada.";
    } else {
        return "Baixo risco de plágio. {$matchCount} correspondências encontradas, mas com baixa similaridade.";
    }
}
/**
 * Valida referências online
 * 
 * @param string $text Texto contendo referências
 * @return array Resultado da validação
 */
function validateReferencesOnline($text) {
    $references = extractReferences($text);
    $validationResults = [];
    foreach ($references as $reference) {
        $validation = validateSingleReference($reference);
        $validationResults[] = $validation;
    }
    $validCount = count(array_filter($validationResults, function($r) {
        return $r['is_valid'];
    }));
    return [
        'total_references' => count($references),
        'valid_references' => $validCount,
        'invalid_references' => count($references) - $validCount,
        'validation_results' => $validationResults,
        'summary' => generateReferenceSummary($validationResults)
    ];
}
/**
 * Extrai referências do texto
 * 
 * @param string $text Texto para analisar
 * @return array Array de referências encontradas
 */
function extractReferences($text) {
    $references = [];
    // Padrões melhorados de referências
    $patterns = [
        // URLs completas
        '/(https?:\/\/[^\s\)]+)/',
        // DOI
        '/(10\.\d{4,}\/[^\s\)]+)/',
        // ISBN
        '/(ISBN[:\s]?[0-9\-]{10,17})/i',
        // Citações acadêmicas (formato ABNT)
        '/([A-ZÁÊÇ][a-záêç\s]+,?\s[A-ZÁÊÇ]\.?\s[A-ZÁÊÇ]\.?\s\([0-9]{4}\)\.?\s[^\.]+\.)/',
        // Citações no texto (Autor, ano)
        '/([A-ZÁÊÇ][a-záêç\s]+,\s[0-9]{4})/',
        // Referências entre parênteses
        '/\(([A-ZÁÊÇ][a-záêç\s]+,\s[0-9]{4})\)/',
        // URLs sem protocolo
        '/(www\.[^\s\)]+)/',
        // Emails
        '/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/',
        // Citações com "apud"
        '/([A-ZÁÊÇ][a-záêç\s]+,\s[0-9]{4},\sapud\s[A-ZÁÊÇ][a-záêç\s]+,\s[0-9]{4})/',
        // Referências de livros
        '/([A-ZÁÊÇ][a-záêç\s]+\.\s[^\.]+\.\s[A-ZÁÊÇ][a-záêç\s]+:\s[A-ZÁÊÇ][a-záêç\s]+,\s[0-9]{4}\.)/',
        // Artigos científicos
        '/([A-ZÁÊÇ][a-záêç\s]+\.\s[^\.]+\.\s[A-ZÁÊÇ][a-záêç\s]+\.\s[0-9]{4};?\s[0-9]+\([0-9]+\):?\s[0-9-]+\.)/'
    ];
    foreach ($patterns as $pattern) {
        preg_match_all($pattern, $text, $matches);
        foreach ($matches[0] as $match) {
            $cleanMatch = trim($match);
            if (strlen($cleanMatch) > 5) { // Filtrar matches muito pequenos
                $references[] = $cleanMatch;
            }
        }
    }
    // Buscar seções de referências específicas
    $referenceSections = [
        '/referências?\s*:?\s*\n(.*?)(?=\n\s*\n|\n\s*[A-ZÁÊÇ]|$)/is',
        '/bibliografia\s*:?\s*\n(.*?)(?=\n\s*\n|\n\s*[A-ZÁÊÇ]|$)/is',
        '/references?\s*:?\s*\n(.*?)(?=\n\s*\n|\n\s*[A-ZÁÊÇ]|$)/is'
    ];
    foreach ($referenceSections as $pattern) {
        if (preg_match($pattern, $text, $matches)) {
            $sectionText = $matches[1];
            // Extrair cada linha como possível referência
            $lines = explode("\n", $sectionText);
            foreach ($lines as $line) {
                $line = trim($line);
                if (strlen($line) > 10 && !empty($line)) {
                    $references[] = $line;
                }
            }
        }
    }
    return array_unique($references);
}
/**
 * Valida uma referência individual
 * 
 * @param string $reference Referência para validar
 * @return array Resultado da validação
 */
function validateSingleReference($reference) {
    $isValid = false;
    $validationMethod = '';
    $details = '';
    // Verificar se é URL
    if (filter_var($reference, FILTER_VALIDATE_URL)) {
        $isValid = checkUrlAccessibility($reference);
        $validationMethod = 'URL Check';
        $details = $isValid ? 'URL acessível' : 'URL não acessível';
    }
    // Verificar se é DOI
    elseif (preg_match('/^10\.\d{4,}\//', $reference)) {
        $isValid = checkDOIValidity($reference);
        $validationMethod = 'DOI Check';
        $details = $isValid ? 'DOI válido' : 'DOI inválido';
    }
    // Verificar se é ISBN
    elseif (preg_match('/ISBN[:\s]?[0-9\-]{10,17}/i', $reference)) {
        $isValid = checkISBNValidity($reference);
        $validationMethod = 'ISBN Check';
        $details = $isValid ? 'ISBN válido' : 'ISBN inválido';
    }
    else {
        $validationMethod = 'Manual Review';
        $details = 'Requer revisão manual';
    }
    return [
        'reference' => $reference,
        'is_valid' => $isValid,
        'validation_method' => $validationMethod,
        'details' => $details
    ];
}
/**
 * Verifica acessibilidade de URL
 * 
 * @param string $url URL para verificar
 * @return bool True se acessível
 */
function checkUrlAccessibility($url) {
    // Simular verificação de URL (em implementação real, usar cURL)
    $headers = @get_headers($url, 1);
    return $headers && strpos($headers[0], '200') !== false;
}
/**
 * Verifica validade de DOI
 * 
 * @param string $doi DOI para verificar
 * @return bool True se válido
 */
function checkDOIValidity($doi) {
    // Simular verificação de DOI (em implementação real, usar API do CrossRef)
    return preg_match('/^10\.\d{4,}\/[^\s]+$/', $doi);
}
/**
 * Verifica validade de ISBN
 * 
 * @param string $isbn ISBN para verificar
 * @return bool True se válido
 */
function checkISBNValidity($isbn) {
    // Remover hífens e espaços
    $isbn = preg_replace('/[^0-9X]/', '', $isbn);
    if (strlen($isbn) == 10) {
        return validateISBN10($isbn);
    } elseif (strlen($isbn) == 13) {
        return validateISBN13($isbn);
    }
    return false;
}
/**
 * Valida ISBN-10
 * 
 * @param string $isbn ISBN-10
 * @return bool True se válido
 */
function validateISBN10($isbn) {
    if (strlen($isbn) != 10) return false;
    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $sum += intval($isbn[$i]) * (10 - $i);
    }
    $checkDigit = 11 - ($sum % 11);
    if ($checkDigit == 11) $checkDigit = 0;
    if ($checkDigit == 10) $checkDigit = 'X';
    return $isbn[9] == $checkDigit;
}
/**
 * Valida ISBN-13
 * 
 * @param string $isbn ISBN-13
 * @return bool True se válido
 */
function validateISBN13($isbn) {
    if (strlen($isbn) != 13) return false;
    $sum = 0;
    for ($i = 0; $i < 12; $i++) {
        $sum += intval($isbn[$i]) * (($i % 2 == 0) ? 1 : 3);
    }
    $checkDigit = 10 - ($sum % 10);
    if ($checkDigit == 10) $checkDigit = 0;
    return $isbn[12] == $checkDigit;
}
/**
 * Gera resumo da validação de referências
 * 
 * @param array $results Resultados da validação
 * @return string Resumo
 */
function generateReferenceSummary($results) {
    $total = count($results);
    $valid = count(array_filter($results, function($r) { return $r['is_valid']; }));
    $invalid = $total - $valid;
    if ($total == 0) {
        return "Nenhuma referência encontrada no texto.";
    }
    $percentage = round(($valid / $total) * 100, 1);
    if ($percentage >= 90) {
        return "Excelente! {$valid} de {$total} referências são válidas ({$percentage}%).";
    } elseif ($percentage >= 70) {
        return "Bom! {$valid} de {$total} referências são válidas ({$percentage}%). Algumas podem precisar de verificação.";
    } elseif ($percentage >= 50) {
        return "Atenção! Apenas {$valid} de {$total} referências são válidas ({$percentage}%). Revisão recomendada.";
    } else {
        return "Problema! Apenas {$valid} de {$total} referências são válidas ({$percentage}%). Revisão necessária.";
    }
}
/**
 * Análise de sentimento do texto
 * 
 * @param string $text Texto para analisar
 * @return array Resultado da análise de sentimento
 */
function analyzeSentiment($text) {
    // Palavras-chave para análise de sentimento em português
    $positiveWords = [
        'bom', 'ótimo', 'excelente', 'fantástico', 'maravilhoso', 'perfeito',
        'positivo', 'benefício', 'vantagem', 'sucesso', 'feliz', 'alegre',
        'satisfeito', 'content', 'orgulhoso', 'proud', 'amor', 'adoro'
    ];
    $negativeWords = [
        'ruim', 'terrível', 'horrível', 'péssimo', 'negativo', 'problema',
        'dificuldade', 'erro', 'falha', 'triste', 'deprimido', 'angustiado',
        'preocupado', 'nervoso', 'ansioso', 'raiva', 'ódio', 'detesto'
    ];
    $text = strtolower($text);
    $words = explode(' ', preg_replace('/[^a-z\s]/', ' ', $text));
    $positiveCount = 0;
    $negativeCount = 0;
    foreach ($words as $word) {
        if (in_array($word, $positiveWords)) {
            $positiveCount++;
        } elseif (in_array($word, $negativeWords)) {
            $negativeCount++;
        }
    }
    $totalWords = count($words);
    $positiveRatio = $positiveCount / max($totalWords, 1);
    $negativeRatio = $negativeCount / max($totalWords, 1);
    $sentiment = 'neutro';
    $confidence = 0;
    if ($positiveRatio > $negativeRatio) {
        $sentiment = 'positivo';
        $confidence = $positiveRatio;
    } elseif ($negativeRatio > $positiveRatio) {
        $sentiment = 'negativo';
        $confidence = $negativeRatio;
    }
    return [
        'sentiment' => $sentiment,
        'confidence' => $confidence,
        'positive_words' => $positiveCount,
        'negative_words' => $negativeCount,
        'neutral_words' => $totalWords - $positiveCount - $negativeCount,
        'analysis' => generateSentimentAnalysis($sentiment, $confidence)
    ];
}
/**
 * Gera análise textual do sentimento
 * 
 * @param string $sentiment Sentimento detectado
 * @param float $confidence Confiança da análise
 * @return string Análise textual
 */
function generateSentimentAnalysis($sentiment, $confidence) {
    $confidencePercent = round($confidence * 100, 1);
    switch ($sentiment) {
        case 'positivo':
            if ($confidence > 0.7) {
                return "Texto claramente positivo com {$confidencePercent}% de confiança. Transmite otimismo e satisfação.";
            } else {
                return "Texto tendendo ao positivo com {$confidencePercent}% de confiança. Tom geralmente otimista.";
            }
        case 'negativo':
            if ($confidence > 0.7) {
                return "Texto claramente negativo com {$confidencePercent}% de confiança. Transmite pessimismo ou insatisfação.";
            } else {
                return "Texto tendendo ao negativo com {$confidencePercent}% de confiança. Tom geralmente pessimista.";
            }
        default:
            return "Texto neutro com {$confidencePercent}% de confiança. Tom equilibrado sem polarização clara.";
    }
}
/**
 * Análise de legibilidade do texto
 * 
 * @param string $text Texto para analisar
 * @return array Resultado da análise de legibilidade
 */
function analyzeReadability($text) {
    $sentences = preg_split('/[.!?]+/', $text);
    $words = explode(' ', preg_replace('/[^a-z\s]/i', ' ', $text));
    $syllables = countSyllables($text);
    $sentenceCount = count(array_filter($sentences, function($s) { return trim($s) !== ''; }));
    $wordCount = count(array_filter($words, function($w) { return trim($w) !== ''; }));
    if ($sentenceCount == 0 || $wordCount == 0) {
        return [
            'readability_score' => 0,
            'difficulty_level' => 'indefinido',
            'analysis' => 'Texto insuficiente para análise de legibilidade.'
        ];
    }
    // Fórmula de Flesch Reading Ease adaptada para português
    $avgWordsPerSentence = $wordCount / $sentenceCount;
    $avgSyllablesPerWord = $syllables / $wordCount;
    $readabilityScore = 206.835 - (1.015 * $avgWordsPerSentence) - (84.6 * $avgSyllablesPerWord);
    $readabilityScore = max(0, min(100, $readabilityScore));
    $difficultyLevel = classifyReadability($readabilityScore);
    return [
        'readability_score' => round($readabilityScore, 1),
        'difficulty_level' => $difficultyLevel,
        'avg_words_per_sentence' => round($avgWordsPerSentence, 1),
        'avg_syllables_per_word' => round($avgSyllablesPerWord, 2),
        'total_sentences' => $sentenceCount,
        'total_words' => $wordCount,
        'total_syllables' => $syllables,
        'analysis' => generateReadabilityAnalysis($readabilityScore, $difficultyLevel)
    ];
}
/**
 * Conta sílabas em texto português
 * 
 * @param string $text Texto para contar sílabas
 * @return int Número de sílabas
 */
function countSyllables($text) {
    $words = explode(' ', preg_replace('/[^a-z\s]/i', ' ', $text));
    $totalSyllables = 0;
    foreach ($words as $word) {
        $word = trim($word);
        if (empty($word)) continue;
        // Contagem aproximada de sílabas
        $vowels = preg_match_all('/[aeiouáéíóúâêôãõ]/i', $word);
        $totalSyllables += max(1, $vowels);
    }
    return $totalSyllables;
}
/**
 * Classifica nível de dificuldade de leitura
 * 
 * @param float $score Score de legibilidade
 * @return string Nível de dificuldade
 */
function classifyReadability($score) {
    if ($score >= 90) return 'Muito Fácil';
    if ($score >= 80) return 'Fácil';
    if ($score >= 70) return 'Razoavelmente Fácil';
    if ($score >= 60) return 'Padrão';
    if ($score >= 50) return 'Razoavelmente Difícil';
    if ($score >= 30) return 'Difícil';
    return 'Muito Difícil';
}
/**
 * Gera análise textual da legibilidade
 * 
 * @param float $score Score de legibilidade
 * @param string $level Nível de dificuldade
 * @return string Análise textual
 */
function generateReadabilityAnalysis($score, $level) {
    $scoreInt = round($score);
    if ($score >= 80) {
        return "Texto muito legível (Score: {$scoreInt}). Adequado para leitores de todas as idades e níveis educacionais.";
    } elseif ($score >= 60) {
        return "Texto de legibilidade moderada (Score: {$scoreInt}). Adequado para leitores com ensino médio.";
    } elseif ($score >= 40) {
        return "Texto de legibilidade baixa (Score: {$scoreInt}). Requer leitores com ensino superior.";
    } else {
        return "Texto de legibilidade muito baixa (Score: {$scoreInt}). Adequado apenas para especialistas.";
    }
}
/**
 * Análise de palavras-chave do texto
 * 
 * @param string $text Texto para analisar
 * @param int $limit Número máximo de palavras-chave
 * @return array Resultado da análise de palavras-chave
 */
function analyzeKeywords($text, $limit = 20) {
    // Normalizar texto mantendo acentos
    $text = mb_strtolower($text, 'UTF-8');
    // Tokenizar preservando acentos e caracteres especiais do português
    $words = preg_split('/[\s\p{P}]+/u', $text);
    $words = array_filter($words, function($word) {
        return strlen($word) >= 3 && !in_array($word, getStopwords());
    });
    // Filtrar palavras válidas (apenas letras, acentos e hífens)
    $validWords = [];
    foreach ($words as $word) {
        $word = trim($word);
        // Verificar se a palavra contém apenas letras, acentos e hífens
        if (preg_match('/^[a-záàâãéèêíìîóòôõúùûçñ\-]+$/i', $word) && 
            strlen($word) >= 3 && 
            !in_array($word, getStopwords())) {
            $validWords[] = $word;
        }
    }
    if (empty($validWords)) {
        // Se não há palavras válidas, usar todas as palavras (exceto stopwords)
        foreach ($words as $word) {
            $word = trim($word);
            if (strlen($word) >= 3 && !in_array($word, getStopwords())) {
                $validWords[] = $word;
            }
        }
    }
    // Contar frequência
    $wordFreq = array_count_values($validWords);
    // Calcular TF-IDF simples
    $totalWords = count($validWords);
    $uniqueWords = count($wordFreq);
    $keywords = [];
    foreach ($wordFreq as $word => $freq) {
        // Incluir todas as palavras válidas, independente da frequência
        $tf = $freq / $totalWords;
        $idf = log($uniqueWords / $freq);
        $tfidf = $tf * $idf;
        $keywords[$word] = [
            'word' => $word,
            'frequency' => $freq,
            'tf' => round($tf, 4),
            'idf' => round($idf, 4),
            'tfidf' => round($tfidf, 4)
        ];
    }
    // Ordenar por TF-IDF
    uasort($keywords, function($a, $b) {
        return $b['tfidf'] <=> $a['tfidf'];
    });
    return array_slice($keywords, 0, $limit, true);
}
/**
 * Função principal para análise avançada completa
 * 
 * @param string $text Texto para analisar
 * @param array $options Opções de análise
 * @return array Resultado completo da análise
 */
function performAdvancedAnalysis($text, $options = []) {
    $defaultOptions = [
        'plagiarismCheck' => true,
        'aiDetection' => true,
        'referenceValidation' => true,
        'sentimentAnalysis' => false,
        'readabilityAnalysis' => false,
        'keywordAnalysis' => false,
        'searchDepth' => 'standard',
        'language' => 'pt',
        'similarityThreshold' => 0.7,
        'maxTextLength' => 100000 // Limite para análise avançada
    ];
    $options = array_merge($defaultOptions, $options);
    // Otimizar texto se muito grande
    $originalLength = strlen($text);
    $wasOptimized = false;
    if ($originalLength > $options['maxTextLength']) {
        $text = optimizeLargeText($text, $options['maxTextLength']);
        $wasOptimized = true;
    }
    $results = [
        'text_length' => strlen($text),
        'original_text_length' => $originalLength,
        'word_count' => str_word_count($text),
        'analysis_timestamp' => date('Y-m-d H:i:s'),
        'options_used' => $options,
        'was_optimized' => $wasOptimized
    ];
    // Verificação de plágio
    if ($options['plagiarismCheck']) {
        $results['plagiarism'] = checkPlagiarismOnline($text, $options);
    }
    // Detecção de IA
    if ($options['aiDetection']) {
        $results['ai_detection'] = detectAIGeneratedText($text);
    }
    // Validação de referências
    if ($options['referenceValidation']) {
        $results['reference_validation'] = validateReferencesOnline($text);
    }
    // Análise de sentimento
    if ($options['sentimentAnalysis']) {
        $results['sentiment'] = analyzeSentiment($text);
    }
    // Análise de legibilidade
    if ($options['readabilityAnalysis']) {
        $results['readability'] = analyzeReadability($text);
    }
    // Análise de palavras-chave
    if ($options['keywordAnalysis']) {
        $results['keywords'] = analyzeKeywords($text);
    }
    // Gerar resumo geral
    $results['summary'] = generateOverallSummary($results);
    return $results;
}
/**
 * Gera resumo geral da análise
 * 
 * @param array $results Resultados da análise
 * @return string Resumo geral
 */
function generateOverallSummary($results) {
    $summary = "Análise completa realizada em " . $results['analysis_timestamp'] . ".\n\n";
    if (isset($results['plagiarism'])) {
        $plagiarism = $results['plagiarism'];
        if ($plagiarism['plagiarism_detected']) {
            $summary .= "⚠️ PLÁGIO DETECTADO: " . $plagiarism['analysis_summary'] . "\n";
        } else {
            $summary .= "✅ Nenhum plágio detectado.\n";
        }
    }
    if (isset($results['ai_detection'])) {
        $ai = $results['ai_detection'];
        if ($ai['is_ai_generated']) {
            $confidence = round($ai['confidence'] * 100, 1);
            $summary .= "🤖 TEXTO GERADO POR IA: {$confidence}% de confiança.\n";
        } else {
            $summary .= "👤 Texto parece ter sido escrito por humano.\n";
        }
    }
    if (isset($results['reference_validation'])) {
        $refs = $results['reference_validation'];
        $summary .= "📚 REFERÊNCIAS: " . $refs['summary'] . "\n";
    }
    if (isset($results['sentiment'])) {
        $sentiment = $results['sentiment'];
        $summary .= "😊 SENTIMENTO: " . ucfirst($sentiment['sentiment']) . " (" . round($sentiment['confidence'] * 100, 1) . "% confiança)\n";
    }
    if (isset($results['readability'])) {
        $readability = $results['readability'];
        $summary .= "📖 LEGIBILIDADE: " . $readability['difficulty_level'] . " (Score: " . $readability['readability_score'] . ")\n";
    }
    return $summary;
}
?>
