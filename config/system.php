<?php
/**
 * Configurações Avançadas do Sistema
 * Centraliza todas as configurações em um local
 * 
 * @version 1.0
 * @author Sistema TCC
 */

return [
    // Configurações de Performance
    'performance' => [
        'max_text_length' => 1000000,
        'min_text_length' => 10,
        'max_processing_time' => 1200,
        'memory_limit' => '2G',
        'execution_time_limit' => 1200,
        'enable_cache' => true,
        'cache_expiry' => 3600, // 1 hora
        'max_cache_size' => '500MB'
    ],
    
    // Configurações de Análise
    'analysis' => [
        'similarity_threshold' => 0.7,
        'min_word_length' => 3,
        'max_keywords' => 20,
        'enable_ai_detection' => true,
        'enable_plagiarism_check' => true,
        'enable_reference_validation' => true,
        'enable_sentiment_analysis' => false,
        'enable_readability_analysis' => false
    ],
    
    // Configurações de Cache
    'cache' => [
        'enabled' => true,
        'directory' => 'cache/',
        'max_size' => '500MB',
        'expiry_time' => 3600,
        'cleanup_frequency' => 0.3 // Remove 30% dos arquivos mais antigos
    ],
    
    // Configurações de Logs
    'logging' => [
        'enabled' => true,
        'level' => 'INFO', // DEBUG, INFO, WARNING, ERROR, CRITICAL
        'directory' => 'logs/',
        'max_file_size' => '10MB',
        'max_files' => 5,
        'log_performance' => true,
        'log_errors' => true,
        'log_analysis' => true
    ],
    
    // Configurações de Segurança
    'security' => [
        'max_upload_size' => '50MB',
        'allowed_file_types' => ['txt', 'md', 'html', 'htm', 'css', 'js', 'json', 'xml', 'csv', 'log', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'],
        'sanitize_input' => true,
        'validate_encoding' => true,
        'rate_limiting' => [
            'enabled' => true,
            'max_requests_per_hour' => 100,
            'max_requests_per_day' => 1000
        ]
    ],
    
    // Configurações de API Externa
    'external_apis' => [
        'plagiarism_check' => [
            'enabled' => true,
            'timeout' => 30,
            'max_retries' => 3
        ],
        'reference_validation' => [
            'enabled' => true,
            'timeout' => 15,
            'max_retries' => 2
        ]
    ],
    
    // Configurações de Otimização
    'optimization' => [
        'enable_text_optimization' => true,
        'optimization_threshold' => 100000, // Otimizar textos maiores que 100KB
        'chunk_size' => 50000,
        'enable_parallel_processing' => false, // Para futuras implementações
        'enable_gpu_acceleration' => false // Para futuras implementações
    ],
    
    // Configurações de Interface
    'ui' => [
        'theme' => 'modern',
        'language' => 'pt-BR',
        'show_performance_stats' => true,
        'show_debug_info' => false,
        'enable_animations' => true,
        'responsive_breakpoints' => [
            'mobile' => 768,
            'tablet' => 1024,
            'desktop' => 1200
        ]
    ],
    
    // Configurações de Desenvolvimento
    'development' => [
        'debug_mode' => false,
        'show_errors' => false,
        'log_queries' => false,
        'enable_profiling' => false,
        'test_mode' => false
    ]
];
