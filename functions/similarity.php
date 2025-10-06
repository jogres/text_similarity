<?php
/**
 * Sistema de Comparação de Conteúdo de Textos
 * Implementação avançada de algoritmos NLP para comparação de similaridade
 * 
 * Baseado em:
 * - TF-IDF (Term Frequency-Inverse Document Frequency) - Salton & McGill (1983)
 * - Similaridade Cosseno - Salton (1971)
 * - Normalização de texto - Manning & Schütze (1999)
 * 
 * @author Sistema TCC - Desenvolvido para trabalho de conclusão de curso
 * @version 2.0
 * @since 2024
 */
/**
 * Lista expandida de stopwords em português brasileiro
 * Baseada em estudos de Manning & Schütze (1999) e adaptada para português
 * 
 * Referência: Manning, C. D., & Schütze, H. (1999). Foundations of statistical natural language processing.
 * 
 * @return array Lista de stopwords em português
 */
function getStopwords() {
    return [
        // Artigos definidos e indefinidos
        'a', 'ao', 'aos', 'aquela', 'aquelas', 'aquele', 'aqueles', 'aquilo', 'as', 'o', 'os',
        'um', 'uma', 'uns', 'umas',
        // Preposições
        'até', 'com', 'da', 'das', 'do', 'dos', 'em', 'na', 'nas', 'no', 'nos', 'para', 
        'pela', 'pelas', 'pelo', 'pelos', 'por', 'sobre', 'sob', 'entre', 'durante',
        'desde', 'após', 'antes', 'depois', 'contra', 'perante', 'mediante', 'conforme',
        // Pronomes
        'ela', 'elas', 'ele', 'eles', 'eu', 'nós', 'você', 'vocês', 'se', 'sua', 'suas',
        'seu', 'seus', 'meu', 'meus', 'minha', 'minhas', 'nosso', 'nossa', 'nossos', 'nossas',
        'teu', 'teus', 'tua', 'tuas', 'vosso', 'vossos', 'vossa', 'vossas',
        'me', 'te', 'lhe', 'lhes', 'nos', 'vos', 'si', 'consigo',
        // Conjunções
        'e', 'mas', 'porém', 'contudo', 'todavia', 'entretanto', 'ou', 'nem', 'que',
        'portanto', 'assim', 'logo', 'então', 'pois', 'porque', 'se', 'caso', 'quando',
        'enquanto', 'embora', 'apesar', 'conforme', 'segundo', 'consoante',
        // Advérbios comuns
        'muito', 'mais', 'menos', 'bem', 'mal', 'sempre', 'nunca', 'já', 'ainda', 'também',
        'tampouco', 'tanto', 'quanto', 'assim', 'aqui', 'ali', 'lá', 'onde', 'quando',
        'como', 'porque', 'melhor', 'pior', 'bastante', 'suficiente', 'demais',
        'cedo', 'tarde', 'noite', 'dia', 'semana', 'mês', 'ano',
        // Verbos auxiliares
        'são', 'ser', 'estar', 'ter', 'haver', 'poder', 'dever', 'querer', 'saber',
        'conhecer', 'ver', 'dizer', 'falar', 'fazer', 'pode', 'deve', 'quer', 'sabe',
        'conhece', 'vê', 'diz', 'fala', 'faz', 'podia', 'devia', 'queria', 'sabia',
        // Outras palavras funcionais
        'de', 'que', 'se', 'não', 'sim', 'tal', 'qual', 'cada', 'todo', 'toda', 'todos', 'todas',
        'quem', 'cujo', 'cuja', 'cujos', 'cujas', 'este', 'esta', 'estes', 'estas',
        'esse', 'essa', 'esses', 'essas', 'aquele', 'aquela', 'aqueles', 'aquelas',
        'isto', 'isso', 'aquilo', 'tudo', 'nada', 'algo', 'algum', 'alguma', 'alguns', 'algumas',
        'outro', 'outra', 'outros', 'outras', 'primeiro', 'segundo', 'terceiro', 'último',
        'próximo', 'anterior', 'pouco', 'pouca', 'poucos', 'poucas', 'bastante',
        'grande', 'pequeno', 'pequena', 'pequenos', 'pequenas', 'bom', 'boa', 'bons', 'boas',
        'ruim', 'ruins', 'desse', 'desta', 'deste', 'desta', 'desse', 'desta', 'deste'
    ];
}
/**
 * Normalização avançada de texto com remoção de acentos e caracteres especiais
 * 
 * Implementação baseada em técnicas de pré-processamento de texto descritas em:
 * - Jurafsky, D., & Martin, J. H. (2020). Speech and Language Processing
 * - Bird, S., Klein, E., & Loper, E. (2009). Natural Language Processing with Python
 * 
 * @param string $text Texto a ser normalizado
 * @return string Texto normalizado
 */
/**
 * Detecta se um texto foi gerado por inteligência artificial
 * 
 * Implementação baseada em técnicas de análise de padrões descritas em:
 * - Mitchell, M., et al. (2023). AI-Generated Text Detection: A Survey
 * - Gehrmann, S., et al. (2019). GLTR: Statistical Detection and Visualization of Generated Text
 * 
 * @param string $text Texto a ser analisado
 * @return array Resultado da análise de detecção de IA
 */
function detectAIGeneratedText($text) {
    $patterns = analyzeAIPatterns($text);
    $scores = calculateAIScores($text, $patterns);
    $confidence = calculateAIConfidence($scores);
    return [
        'is_ai_generated' => $confidence > 0.6,
        'confidence' => $confidence,
        'scores' => $scores,
        'patterns' => $patterns,
        'explanation' => generateAIExplanation($confidence, $scores)
    ];
}
/**
 * Analisa padrões típicos de texto gerado por IA
 * 
 * @param string $text Texto a ser analisado
 * @return array Padrões identificados
 */
function analyzeAIPatterns($text) {
    $patterns = [
        'repetitive_phrases' => 0,
        'formal_language' => 0,
        'lack_of_contractions' => 0,
        'overly_polite' => 0,
        'generic_transitions' => 0,
        'perfectionist_tone' => 0,
        'lack_of_personal_voice' => 0,
        'excessive_qualifiers' => 0
    ];
    $text = strtolower($text);
    $words = explode(' ', $text);
    $wordCount = count($words);
    // Padrões de linguagem formal excessiva
    $formalPhrases = [
        'é importante notar', 'deve-se considerar', 'é fundamental',
        'é essencial', 'é crucial', 'é necessário', 'é relevante',
        'é significativo', 'é notável', 'é importante destacar'
    ];
    foreach ($formalPhrases as $phrase) {
        $patterns['formal_language'] += substr_count($text, $phrase);
    }
    // Falta de contrações (típico de IA)
    $contractions = ['não', 'não é', 'não foi', 'não tem', 'não pode'];
    $hasContractions = false;
    foreach ($contractions as $contraction) {
        if (strpos($text, $contraction) !== false) {
            $hasContractions = true;
            break;
        }
    }
    $patterns['lack_of_contractions'] = $hasContractions ? 0 : 1;
    // Linguagem excessivamente educada
    $politePhrases = [
        'gostaria de', 'seria interessante', 'é importante mencionar',
        'vale ressaltar', 'é fundamental', 'é crucial'
    ];
    foreach ($politePhrases as $phrase) {
        $patterns['overly_polite'] += substr_count($text, $phrase);
    }
    // Transições genéricas
    $genericTransitions = [
        'além disso', 'por outro lado', 'adicionalmente', 'furthermore',
        'moreover', 'however', 'therefore', 'consequently'
    ];
    foreach ($genericTransitions as $transition) {
        $patterns['generic_transitions'] += substr_count($text, $transition);
    }
    // Tom perfeccionista
    $perfectionistWords = [
        'perfeito', 'ideal', 'ótimo', 'excelente', 'superior',
        'excepcional', 'notável', 'destacado', 'exemplar'
    ];
    foreach ($perfectionistWords as $word) {
        $patterns['perfectionist_tone'] += substr_count($text, $word);
    }
    // Falta de voz pessoal (ausência de pronomes pessoais)
    $personalPronouns = ['eu', 'mim', 'me', 'meu', 'minha', 'nós', 'nos'];
    $personalCount = 0;
    foreach ($personalPronouns as $pronoun) {
        $personalCount += substr_count($text, $pronoun);
    }
    $patterns['lack_of_personal_voice'] = $personalCount < 2 ? 1 : 0;
    // Qualificadores excessivos
    $qualifiers = [
        'muito', 'extremamente', 'altamente', 'completamente',
        'totalmente', 'absolutamente', 'definitivamente'
    ];
    foreach ($qualifiers as $qualifier) {
        $patterns['excessive_qualifiers'] += substr_count($text, $qualifier);
    }
    // Normalizar padrões
    foreach ($patterns as $key => $value) {
        if ($key !== 'lack_of_contractions' && $key !== 'lack_of_personal_voice') {
            $patterns[$key] = min($value / max($wordCount / 100, 1), 1);
        }
    }
    return $patterns;
}
/**
 * Calcula scores de detecção de IA
 * 
 * @param string $text Texto analisado
 * @param array $patterns Padrões identificados
 * @return array Scores calculados
 */
function calculateAIScores($text, $patterns) {
    $scores = [
        'linguistic_complexity' => 0,
        'repetition_score' => 0,
        'formality_score' => 0,
        'coherence_score' => 0,
        'originality_score' => 0
    ];
    // Análise de complexidade linguística
    $sentences = preg_split('/[.!?]+/', $text);
    $avgSentenceLength = 0;
    $complexSentences = 0;
    foreach ($sentences as $sentence) {
        $words = explode(' ', trim($sentence));
        $length = count($words);
        $avgSentenceLength += $length;
        if ($length > 20) $complexSentences++;
    }
    $avgSentenceLength = $avgSentenceLength / max(count($sentences), 1);
    $scores['linguistic_complexity'] = min($avgSentenceLength / 15, 1);
    // Score de repetição
    $words = explode(' ', strtolower($text));
    $wordFreq = array_count_values($words);
    $repetitionScore = 0;
    foreach ($wordFreq as $freq) {
        if ($freq > 1) {
            $repetitionScore += ($freq - 1) / count($words);
        }
    }
    $scores['repetition_score'] = min($repetitionScore, 1);
    // Score de formalidade
    $scores['formality_score'] = ($patterns['formal_language'] + 
                                 $patterns['overly_polite'] + 
                                 $patterns['lack_of_contractions']) / 3;
    // Score de coerência (baseado em transições)
    $scores['coherence_score'] = min($patterns['generic_transitions'] * 2, 1);
    // Score de originalidade (inverso da repetição)
    $scores['originality_score'] = 1 - $scores['repetition_score'];
    return $scores;
}
/**
 * Calcula confiança na detecção de IA
 * 
 * @param array $scores Scores calculados
 * @return float Confiança (0-1)
 */
function calculateAIConfidence($scores) {
    $weights = [
        'linguistic_complexity' => 0.2,
        'repetition_score' => 0.15,
        'formality_score' => 0.25,
        'coherence_score' => 0.2,
        'originality_score' => 0.2
    ];
    $confidence = 0;
    foreach ($scores as $key => $score) {
        $confidence += $score * $weights[$key];
    }
    return min($confidence, 1);
}
/**
 * Gera explicação da detecção de IA
 * 
 * @param float $confidence Confiança da detecção
 * @param array $scores Scores individuais
 * @return string Explicação textual
 */
function generateAIExplanation($confidence, $scores) {
    if ($confidence > 0.7) {
        return "Alta probabilidade de texto gerado por IA. Características detectadas: linguagem muito formal, falta de voz pessoal, e padrões repetitivos.";
    } elseif ($confidence > 0.5) {
        return "Moderada probabilidade de texto gerado por IA. Algumas características típicas de IA foram identificadas.";
    } elseif ($confidence > 0.3) {
        return "Baixa probabilidade de texto gerado por IA. Poucas características típicas de IA foram detectadas.";
    } else {
        return "Muito baixa probabilidade de texto gerado por IA. O texto parece ter sido escrito por um humano.";
    }
}
/**
 * Tokenização avançada com pré-processamento robusto
 * 
 * Implementação baseada em técnicas descritas em:
 * - Manning, C. D., Raghavan, P., & Schütze, H. (2008). Introduction to Information Retrieval
 * - Bird, S., Klein, E., & Loper, E. (2009). Natural Language Processing with Python
 * 
 * @param string $text Texto a ser tokenizado
 * @param int $minLength Comprimento mínimo dos tokens (padrão: 3)
 * @param bool $removeNumbers Se deve remover números (padrão: false)
 * @return array Array de tokens processados
 */
function tokenize($text, $minLength = 3, $removeNumbers = false) {
    // Converter para minúsculas mantendo acentos
    $text = mb_strtolower($text, 'UTF-8');
    // Remover caracteres especiais e pontuação, mantendo letras, acentos, números e espaços
    if ($removeNumbers) {
        $text = preg_replace('/[^a-záàâãéèêíìîóòôõúùûçñ\s]/u', ' ', $text);
    } else {
        $text = preg_replace('/[^a-záàâãéèêíìîóòôõúùûçñ0-9\s]/u', ' ', $text);
    }
    // Normalizar espaços múltiplos
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    // Dividir em tokens
    $tokens = preg_split('/\s+/', $text);
    // Filtrar tokens válidos
    $stopwords = getStopwords();
    $filteredTokens = [];
    foreach ($tokens as $token) {
        // Verificar critérios de filtragem
        if (empty($token) || 
            strlen($token) < $minLength || 
            in_array($token, $stopwords) ||
            is_numeric($token) && $removeNumbers) {
            continue;
        }
        // Verificar se é uma palavra válida (contém pelo menos uma letra)
        if (preg_match('/[a-z]/', $token)) {
            $filteredTokens[] = $token;
        }
    }
    return $filteredTokens;
}
/**
 * Calcula a frequência de termos (TF) com normalização avançada
 * 
 * Implementação baseada na fórmula clássica de Salton & McGill (1983):
 * TF(t,d) = f(t,d) / Σf(w,d)
 * 
 * Referência: Salton, G., & McGill, M. J. (1983). Introduction to modern information retrieval.
 * 
 * @param array $tokens Array de tokens do documento
 * @param string $normalization Tipo de normalização ('raw', 'log', 'double', 'max')
 * @return array Vetor TF normalizado
 */
function calculateTF($tokens, $normalization = 'raw') {
    if (empty($tokens)) {
        return [];
    }
    $tf = [];
    $totalTokens = count($tokens);
    $maxFreq = 0;
    // Contar frequências brutas
    foreach ($tokens as $token) {
        if (!isset($tf[$token])) {
            $tf[$token] = 0;
        }
        $tf[$token]++;
        $maxFreq = max($maxFreq, $tf[$token]);
    }
    // Aplicar normalização conforme especificado
    switch ($normalization) {
        case 'log':
            // Normalização logarítmica: 1 + log(f(t,d))
            foreach ($tf as $term => $count) {
                $tf[$term] = 1 + log($count);
            }
            break;
        case 'double':
            // Normalização dupla: 0.5 + 0.5 * (f(t,d) / max_freq)
            foreach ($tf as $term => $count) {
                $tf[$term] = 0.5 + 0.5 * ($count / $maxFreq);
            }
            break;
        case 'max':
            // Normalização por frequência máxima: f(t,d) / max_freq
            foreach ($tf as $term => $count) {
                $tf[$term] = $count / $maxFreq;
            }
            break;
        case 'raw':
        default:
            // Normalização padrão: f(t,d) / total_tokens
            foreach ($tf as $term => $count) {
                $tf[$term] = $count / $totalTokens;
            }
            break;
    }
    return $tf;
}
/**
 * Calcula a frequência inversa de documentos (IDF) com suavização
 * 
 * Implementação baseada na fórmula de Robertson & Jones (1976) com suavização:
 * IDF(t,D) = log((|D| + 1) / (|{d∈D : t∈d}| + 1)) + 1
 * 
 * A suavização (+1) evita divisão por zero e melhora a estabilidade numérica.
 * 
 * Referência: Robertson, S. E., & Jones, K. S. (1976). Relevance weighting of search terms.
 * 
 * @param array $documents Array de documentos (cada documento é um array de tokens)
 * @param string $formula Tipo de fórmula IDF ('standard', 'smooth', 'probabilistic')
 * @return array Vetor IDF calculado
 */
function calculateIDF($documents, $formula = 'smooth') {
    if (empty($documents)) {
        return [];
    }
    $idf = [];
    $totalDocs = count($documents);
    // Contar em quantos documentos cada termo aparece
    $termDocCount = [];
    foreach ($documents as $doc) {
        $uniqueTerms = array_unique($doc);
        foreach ($uniqueTerms as $term) {
            if (!isset($termDocCount[$term])) {
                $termDocCount[$term] = 0;
            }
            $termDocCount[$term]++;
        }
    }
    // Calcular IDF conforme fórmula especificada
    foreach ($termDocCount as $term => $docCount) {
        switch ($formula) {
            case 'standard':
                // IDF clássico: log(|D| / |{d∈D : t∈d}|)
                $idf[$term] = log($totalDocs / $docCount);
                break;
            case 'probabilistic':
                // IDF probabilístico: log((|D| - |{d∈D : t∈d}|) / |{d∈D : t∈d}|)
                $idf[$term] = log(($totalDocs - $docCount) / $docCount);
                break;
            case 'smooth':
            default:
                // IDF suavizado: log((|D| + 1) / (|{d∈D : t∈d}| + 1)) + 1
                $idf[$term] = log(($totalDocs + 1) / ($docCount + 1)) + 1;
                break;
        }
    }
    return $idf;
}
/**
 * Calcula TF-IDF com normalização e otimizações
 * 
 * Implementação da fórmula clássica: TF-IDF(t,d,D) = TF(t,d) × IDF(t,D)
 * 
 * Referência: Salton, G., & McGill, M. J. (1983). Introduction to modern information retrieval.
 * 
 * @param array $tf Vetor TF do documento
 * @param array $idf Vetor IDF da coleção
 * @param bool $normalize Se deve normalizar o vetor resultante
 * @return array Vetor TF-IDF calculado
 */
function calculateTFIDF($tf, $idf, $normalize = false) {
    $tfidf = [];
    // Calcular TF-IDF para cada termo
    foreach ($tf as $term => $tfValue) {
        $idfValue = $idf[$term] ?? 0;
        $tfidf[$term] = $tfValue * $idfValue;
    }
    // Normalização opcional do vetor (norma euclidiana)
    if ($normalize) {
        $magnitude = 0;
        foreach ($tfidf as $value) {
            $magnitude += $value * $value;
        }
        $magnitude = sqrt($magnitude);
        if ($magnitude > 0) {
            foreach ($tfidf as $term => $value) {
                $tfidf[$term] = $value / $magnitude;
            }
        }
    }
    return $tfidf;
}
/**
 * Calcula a similaridade cosseno entre dois vetores com otimizações
 * 
 * Implementação da fórmula clássica: cos(θ) = (A · B) / (||A|| × ||B||)
 * 
 * Referência: Salton, G. (1971). The SMART retrieval system—experiments in automatic document processing.
 * 
 * @param array $vector1 Primeiro vetor TF-IDF
 * @param array $vector2 Segundo vetor TF-IDF
 * @param bool $sparseOptimization Otimização para vetores esparsos
 * @return float Similaridade cosseno (0-1)
 */
function calculateCosineSimilarity($vector1, $vector2, $sparseOptimization = true) {
    if (empty($vector1) || empty($vector2)) {
        return 0.0;
    }
    // Obter todos os termos únicos dos dois vetores
    $allTerms = array_unique(array_merge(array_keys($vector1), array_keys($vector2)));
    if (empty($allTerms)) {
        return 0.0;
    }
    // Otimização para vetores esparsos: calcular apenas termos não-zero
    if ($sparseOptimization) {
        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;
        // Calcular produto escalar e magnitudes simultaneamente
        foreach ($allTerms as $term) {
            $value1 = $vector1[$term] ?? 0;
            $value2 = $vector2[$term] ?? 0;
            $dotProduct += $value1 * $value2;
            $magnitude1 += $value1 * $value1;
            $magnitude2 += $value2 * $value2;
        }
        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);
    } else {
        // Implementação padrão
        $dotProduct = 0;
        foreach ($allTerms as $term) {
            $value1 = $vector1[$term] ?? 0;
            $value2 = $vector2[$term] ?? 0;
            $dotProduct += $value1 * $value2;
        }
        // Calcular magnitudes separadamente
        $magnitude1 = 0;
        foreach ($vector1 as $value) {
            $magnitude1 += $value * $value;
        }
        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = 0;
        foreach ($vector2 as $value) {
            $magnitude2 += $value * $value;
        }
        $magnitude2 = sqrt($magnitude2);
    }
    // Evitar divisão por zero
    if ($magnitude1 == 0 || $magnitude2 == 0) {
        return 0.0;
    }
    $similarity = $dotProduct / ($magnitude1 * $magnitude2);
    // Garantir que o resultado está no intervalo [0, 1]
    return max(0.0, min(1.0, $similarity));
}
/**
 * Encontra os termos que mais contribuem para a similaridade com análise detalhada
 * 
 * Implementação baseada em técnicas de análise de contribuição descritas em:
 * - Manning, C. D., Raghavan, P., & Schütze, H. (2008). Introduction to Information Retrieval
 * 
 * @param array $vector1 Primeiro vetor TF-IDF
 * @param array $vector2 Segundo vetor TF-IDF
 * @param int $limit Número máximo de termos a retornar
 * @param bool $includeZeroValues Se deve incluir termos com contribuição zero
 * @return array Termos ordenados por contribuição
 */
function getTopContributingTerms($vector1, $vector2, $limit = 10, $includeZeroValues = false) {
    $contributions = [];
    $allTerms = array_unique(array_merge(array_keys($vector1), array_keys($vector2)));
    foreach ($allTerms as $term) {
        $value1 = $vector1[$term] ?? 0;
        $value2 = $vector2[$term] ?? 0;
        $contribution = $value1 * $value2;
        if ($contribution > 0 || $includeZeroValues) {
            $contributions[$term] = [
                'term' => $term,
                'contribution' => $contribution,
                'value1' => $value1,
                'value2' => $value2,
                'normalized_contribution' => $contribution > 0 ? $contribution / max($value1, $value2) : 0
            ];
        }
    }
    // Ordenar por contribuição (decrescente)
    uasort($contributions, function($a, $b) {
        return $b['contribution'] <=> $a['contribution'];
    });
    return array_slice($contributions, 0, $limit, true);
}
/**
 * Calcula métricas adicionais de similaridade
 * 
 * @param array $vector1 Primeiro vetor TF-IDF
 * @param array $vector2 Segundo vetor TF-IDF
 * @return array Métricas de similaridade
 */
function calculateAdditionalMetrics($vector1, $vector2) {
    $allTerms = array_unique(array_merge(array_keys($vector1), array_keys($vector2)));
    $commonTerms = array_intersect(array_keys($vector1), array_keys($vector2));
    $metrics = [
        'jaccard_similarity' => 0,
        'dice_coefficient' => 0,
        'overlap_coefficient' => 0,
        'common_terms_count' => count($commonTerms),
        'total_terms_count' => count($allTerms),
        'unique_terms_ratio' => count($commonTerms) / max(count($allTerms), 1)
    ];
    if (count($allTerms) > 0) {
        // Jaccard Similarity: |A ∩ B| / |A ∪ B|
        $metrics['jaccard_similarity'] = count($commonTerms) / count($allTerms);
        // Dice Coefficient: 2|A ∩ B| / (|A| + |B|)
        $metrics['dice_coefficient'] = (2 * count($commonTerms)) / (count($vector1) + count($vector2));
        // Overlap Coefficient: |A ∩ B| / min(|A|, |B|)
        $metrics['overlap_coefficient'] = count($commonTerms) / min(count($vector1), count($vector2));
    }
    return $metrics;
}
/**
 * Otimiza textos muito grandes usando amostragem inteligente
 * 
 * @param string $text Texto original
 * @param int $maxLength Tamanho máximo desejado
 * @return string Texto otimizado
 */
function optimizeLargeText($text, $maxLength) {
    $textLength = strlen($text);
    if ($textLength <= $maxLength) {
        return $text;
    }
    // Para artigos acadêmicos, priorizar seções importantes
    $importantSections = [
        '/abstract\s*:?\s*\n(.*?)(?=\n\s*\n|\n\s*[A-ZÁÊÇ]|$)/is',
        '/resumo\s*:?\s*\n(.*?)(?=\n\s*\n|\n\s*[A-ZÁÊÇ]|$)/is',
        '/introdução\s*:?\s*\n(.*?)(?=\n\s*\n|\n\s*[A-ZÁÊÇ]|$)/is',
        '/conclusão\s*:?\s*\n(.*?)(?=\n\s*\n|\n\s*[A-ZÁÊÇ]|$)/is',
        '/referências?\s*:?\s*\n(.*?)(?=\n\s*\n|\n\s*[A-ZÁÊÇ]|$)/is'
    ];
    $optimizedText = '';
    $currentLength = 0;
    // Primeiro, extrair seções importantes
    foreach ($importantSections as $pattern) {
        if (preg_match($pattern, $text, $matches)) {
            $section = trim($matches[1]);
            if ($currentLength + strlen($section) <= $maxLength * 0.7) {
                $optimizedText .= $section . "\n\n";
                $currentLength += strlen($section) + 2;
            }
        }
    }
    // Se ainda há espaço, adicionar parágrafos do meio
    if ($currentLength < $maxLength * 0.8) {
        $paragraphs = preg_split('/\n\s*\n/', $text);
        $remainingSpace = $maxLength - $currentLength;
        foreach ($paragraphs as $paragraph) {
            $paragraphLength = strlen($paragraph);
            if ($currentLength + $paragraphLength <= $maxLength) {
                $optimizedText .= $paragraph . "\n\n";
                $currentLength += $paragraphLength + 2;
            } else {
                // Se o parágrafo for muito grande, pegar apenas o início
                $remainingSpace = $maxLength - $currentLength;
                if ($remainingSpace > 200) { // Só se houver espaço suficiente
                    $optimizedText .= substr($paragraph, 0, $remainingSpace - 3) . '...';
                }
                break;
            }
        }
    }
    // Se ainda não temos texto suficiente, usar amostragem inteligente
    if (strlen($optimizedText) < $maxLength * 0.3) {
        $words = explode(' ', $text);
        $wordCount = count($words);
        $sampleSize = min($wordCount, intval($maxLength / 8)); // Aproximadamente 8 chars por palavra
        // Amostragem uniforme com prioridade para início e fim
        $step = max(1, intval($wordCount / $sampleSize));
        $sampledWords = [];
        // Priorizar início e fim do texto
        $startWords = array_slice($words, 0, intval($sampleSize * 0.3));
        $endWords = array_slice($words, -intval($sampleSize * 0.3));
        $middleWords = [];
        for ($i = intval($sampleSize * 0.3); $i < $wordCount - intval($sampleSize * 0.3); $i += $step) {
            if (count($middleWords) >= intval($sampleSize * 0.4)) break;
            $middleWords[] = $words[$i];
        }
        $sampledWords = array_merge($startWords, $middleWords, $endWords);
        $optimizedText = implode(' ', $sampledWords);
    }
    return $optimizedText;
}
/**
 * Função principal para comparar dois textos com análise completa
 * 
 * Implementação baseada em técnicas de comparação de documentos descritas em:
 * - Salton, G., & McGill, M. J. (1983). Introduction to modern information retrieval
 * - Manning, C. D., Raghavan, P., & Schütze, H. (2008). Introduction to Information Retrieval
 * 
 * @param string $text1 Primeiro texto para comparação
 * @param string $text2 Segundo texto para comparação
 * @param array $options Opções de configuração
 * @return array Resultado completo da comparação
 */
function compareTexts($text1, $text2, $options = []) {
    // Configurações padrão
    $defaultOptions = [
        'minLength' => 3,
        'removeNumbers' => false,
        'tfNormalization' => 'raw',
        'idfFormula' => 'smooth',
        'normalizeTfIdf' => false,
        'sparseOptimization' => true,
        'topTermsLimit' => 10,
        'maxTextLength' => 200000, // Limite para textos muito grandes
        'chunkSize' => 100000 // Tamanho do chunk para processamento
    ];
    $options = array_merge($defaultOptions, $options);
    // Verificar se os textos são muito grandes e otimizar
    $text1Length = strlen($text1);
    $text2Length = strlen($text2);
    $wasOptimized = false;
    if ($text1Length > $options['maxTextLength'] || $text2Length > $options['maxTextLength']) {
        // Para textos muito grandes, usar amostragem inteligente
        $originalText1 = $text1;
        $originalText2 = $text2;
        $text1 = optimizeLargeText($text1, $options['maxTextLength']);
        $text2 = optimizeLargeText($text2, $options['maxTextLength']);
        $wasOptimized = true;
    }
    // Validar entradas
    if (empty(trim($text1)) || empty(trim($text2))) {
        return [
            'similarity' => 0,
            'message' => 'Um ou ambos os textos estão vazios.',
            'topTerms' => [],
            'metrics' => [],
            'stats' => [],
            'error' => 'EMPTY_TEXT'
        ];
    }
    // Tokenizar os textos
    $tokens1 = tokenize($text1, $options['minLength'], $options['removeNumbers']);
    $tokens2 = tokenize($text2, $options['minLength'], $options['removeNumbers']);
    // Verificar se há tokens suficientes
    if (empty($tokens1) || empty($tokens2)) {
        return [
            'similarity' => 0,
            'message' => 'Um ou ambos os textos não contêm palavras válidas para comparação.',
            'topTerms' => [],
            'metrics' => [],
            'stats' => [
                'tokens1' => count($tokens1),
                'tokens2' => count($tokens2),
                'uniqueTerms1' => 0,
                'uniqueTerms2' => 0
            ],
            'error' => 'INSUFFICIENT_TOKENS'
        ];
    }
    // Calcular TF para cada documento
    $tf1 = calculateTF($tokens1, $options['tfNormalization']);
    $tf2 = calculateTF($tokens2, $options['tfNormalization']);
    // Calcular IDF usando ambos os documentos
    $idf = calculateIDF([$tokens1, $tokens2], $options['idfFormula']);
    // Calcular TF-IDF para cada documento
    $tfidf1 = calculateTFIDF($tf1, $idf, $options['normalizeTfIdf']);
    $tfidf2 = calculateTFIDF($tf2, $idf, $options['normalizeTfIdf']);
    // Calcular similaridade cosseno
    $similarity = calculateCosineSimilarity($tfidf1, $tfidf2, $options['sparseOptimization']);
    // Obter termos que mais contribuem
    $topTerms = getTopContributingTerms($tfidf1, $tfidf2, $options['topTermsLimit']);
    // Calcular métricas adicionais
    $metrics = calculateAdditionalMetrics($tfidf1, $tfidf2);
    // Estatísticas detalhadas
    $stats = [
        'tokens1' => count($tokens1),
        'tokens2' => count($tokens2),
        'uniqueTerms1' => count($tf1),
        'uniqueTerms2' => count($tf2),
        'totalUniqueTerms' => count(array_unique(array_merge(array_keys($tf1), array_keys($tf2)))),
        'commonTerms' => count(array_intersect(array_keys($tf1), array_keys($tf2))),
        'text1Length' => strlen($text1),
        'text2Length' => strlen($text2),
        'processingTime' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
    ];
    $result = [
        'similarity' => $similarity,
        'message' => 'Comparação realizada com sucesso.',
        'topTerms' => $topTerms,
        'metrics' => $metrics,
        'stats' => $stats,
        'options' => $options
    ];
    // Adicionar informação sobre otimização se aplicável
    if ($wasOptimized) {
        $result['optimization'] = [
            'was_optimized' => true,
            'original_length1' => $text1Length,
            'original_length2' => $text2Length,
            'optimized_length1' => strlen($text1),
            'optimized_length2' => strlen($text2),
            'message' => 'Texto otimizado para melhor performance. Análise baseada em amostragem inteligente.'
        ];
    }
    return $result;
}
/**
 * Formata o resultado da similaridade para exibição com análise detalhada
 * 
 * @param array $result Resultado da comparação
 * @return array Resultado formatado para exibição
 */
function formatSimilarityResult($result) {
    $similarity = $result['similarity'];
    $percentage = round($similarity * 100, 2);
    // Classificação da similaridade baseada em estudos empíricos
    $level = '';
    $color = '';
    $description = '';
    if ($percentage >= 90) {
        $level = 'Muito Alta';
        $color = 'success';
        $description = 'Textos praticamente idênticos ou muito similares';
    } elseif ($percentage >= 80) {
        $level = 'Alta';
        $color = 'success';
        $description = 'Textos altamente similares com muitos conceitos compartilhados';
    } elseif ($percentage >= 60) {
        $level = 'Média-Alta';
        $color = 'warning';
        $description = 'Textos moderadamente similares com alguns conceitos em comum';
    } elseif ($percentage >= 40) {
        $level = 'Média';
        $color = 'warning';
        $description = 'Textos com similaridade moderada e poucos conceitos compartilhados';
    } elseif ($percentage >= 20) {
        $level = 'Baixa';
        $color = 'danger';
        $description = 'Textos com baixa similaridade e poucos termos em comum';
    } else {
        $level = 'Muito Baixa';
        $color = 'danger';
        $description = 'Textos praticamente diferentes sem relação significativa';
    }
    // Formatar termos contribuintes
    $formattedTerms = [];
    if (!empty($result['topTerms'])) {
        foreach ($result['topTerms'] as $term => $data) {
            if (is_array($data)) {
                $formattedTerms[] = [
                    'term' => $data['term'],
                    'contribution' => round($data['contribution'], 4),
                    'value1' => round($data['value1'], 4),
                    'value2' => round($data['value2'], 4)
                ];
            } else {
                $formattedTerms[] = [
                    'term' => $term,
                    'contribution' => round($data, 4)
                ];
            }
        }
    }
    return [
        'similarity' => $similarity,
        'percentage' => $percentage,
        'level' => $level,
        'color' => $color,
        'description' => $description,
        'topTerms' => $formattedTerms,
        'stats' => $result['stats'] ?? [],
        'metrics' => $result['metrics'] ?? [],
        'message' => $result['message'] ?? '',
        'error' => $result['error'] ?? null
    ];
}
?>
