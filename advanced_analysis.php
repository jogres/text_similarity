<?php
/**
 * Sistema de Análise Avançada de Textos
 * Análise completa com verificação de plágio, detecção de IA e validação de referências
 */

// Incluir arquivos de funções
require_once 'functions/similarity.php';
require_once 'functions/file_processor.php';
require_once 'functions/advanced_analysis.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análise Avançada de Textos - Sistema de Comparação</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/advanced.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>🔍 Análise Avançada de Textos</h1>
            <p>Análise completa com verificação de plágio, detecção de IA e validação de referências online</p>
        </header>

        <main class="main-content">
            <div class="analysis-tabs">
                <button class="tab-button active" onclick="switchAnalysisTab('upload')">
                    📄 Upload de Arquivo
                </button>
                <button class="tab-button" onclick="switchAnalysisTab('text')">
                    ✍️ Digitar Texto
                </button>
                <button class="tab-button" onclick="switchAnalysisTab('url')">
                    🌐 URL/Website
                </button>
            </div>

            <form action="process_advanced.php" method="POST" enctype="multipart/form-data" class="advanced-form">
                <!-- Aba de Upload -->
                <div id="upload-tab" class="tab-content active">
                    <div class="input-section">
                        <h3>📁 Upload de Arquivo para Análise</h3>
                        <div class="file-upload-area advanced" id="file-upload-area">
                            <input type="file" id="analysisFile" name="analysisFile" accept=".txt,.md,.html,.htm,.css,.js,.json,.xml,.csv,.log,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" style="display: none;">
                            <div class="file-upload-content">
                                <span class="file-icon">📄</span>
                                <p>Clique para selecionar ou arraste um arquivo</p>
                                <p class="file-info">Formatos suportados: .txt, .md, .html, .css, .js, .json, .xml, .csv, .log, .pdf, .doc, .docx, .xls, .xlsx, .ppt, .pptx (Máximo: 10MB)</p>
                            </div>
                            <div class="file-selected" style="display: none;">
                                <span class="file-name"></span>
                                <button type="button" class="remove-file">✕</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aba de Texto -->
                <div id="text-tab" class="tab-content">
                    <div class="input-section">
                        <h3>✍️ Digitar Texto para Análise</h3>
                        <div class="form-group">
                            <label for="analysisText">Texto para Análise:</label>
                            <textarea 
                                id="analysisText" 
                                name="analysisText" 
                                placeholder="Digite ou cole o texto que deseja analisar aqui..."
                                maxlength="100000"
                                rows="15"
                            ></textarea>
                            <div class="char-counter">
                                <span id="text-counter">0</span> / 100.000 caracteres
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aba de URL -->
                <div id="url-tab" class="tab-content">
                    <div class="input-section">
                        <h3>🌐 Análise de Website/URL</h3>
                        <div class="form-group">
                            <label for="analysisUrl">URL do Website:</label>
                            <input 
                                type="url" 
                                id="analysisUrl" 
                                name="analysisUrl" 
                                placeholder="https://exemplo.com/artigo"
                                class="url-input"
                            >
                            <p class="url-help">Digite a URL completa do website que deseja analisar</p>
                        </div>
                    </div>
                </div>

                <!-- Opções de Análise -->
                <div class="analysis-options">
                    <h3>🔧 Opções de Análise</h3>
                    <div class="options-grid">
                        <div class="option-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="plagiarismCheck" value="1" checked>
                                <span class="checkmark"></span>
                                🔍 Verificação de Plágio Online
                            </label>
                            <p class="option-description">Busca por conteúdo similar na internet</p>
                        </div>

                        <div class="option-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="aiDetection" value="1" checked>
                                <span class="checkmark"></span>
                                🤖 Detecção de IA
                            </label>
                            <p class="option-description">Verifica se o texto foi gerado por inteligência artificial</p>
                        </div>

                        <div class="option-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="referenceValidation" value="1" checked>
                                <span class="checkmark"></span>
                                📚 Validação de Referências
                            </label>
                            <p class="option-description">Verifica se as referências citadas são válidas</p>
                        </div>

                        <div class="option-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="sentimentAnalysis" value="1">
                                <span class="checkmark"></span>
                                😊 Análise de Sentimento
                            </label>
                            <p class="option-description">Analisa o tom e sentimento do texto</p>
                        </div>

                        <div class="option-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="readabilityAnalysis" value="1">
                                <span class="checkmark"></span>
                                📖 Análise de Legibilidade
                            </label>
                            <p class="option-description">Avalia a complexidade e legibilidade do texto</p>
                        </div>

                        <div class="option-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="keywordAnalysis" value="1">
                                <span class="checkmark"></span>
                                🔑 Análise de Palavras-chave
                            </label>
                            <p class="option-description">Identifica palavras-chave e termos importantes</p>
                        </div>
                    </div>
                </div>

                <!-- Configurações Avançadas -->
                <div class="advanced-settings">
                    <h3>⚙️ Configurações Avançadas</h3>
                    <div class="settings-grid">
                        <div class="setting-group">
                            <label for="searchDepth">Profundidade da Busca:</label>
                            <select id="searchDepth" name="searchDepth">
                                <option value="basic">Básica (Rápida)</option>
                                <option value="standard" selected>Padrão (Recomendada)</option>
                                <option value="deep">Profunda (Mais Lenta)</option>
                            </select>
                        </div>

                        <div class="setting-group">
                            <label for="language">Idioma do Texto:</label>
                            <select id="language" name="language">
                                <option value="pt">Português</option>
                                <option value="en">English</option>
                                <option value="es">Español</option>
                                <option value="fr">Français</option>
                            </select>
                        </div>

                        <div class="setting-group">
                            <label for="similarityThreshold">Limiar de Similaridade:</label>
                            <input type="range" id="similarityThreshold" name="similarityThreshold" 
                                   min="0.1" max="1.0" step="0.1" value="0.7">
                            <span class="threshold-value">70%</span>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-analyze">
                        <span class="btn-text">Iniciar Análise Avançada</span>
                        <span class="btn-icon">🔍</span>
                    </button>
                    <button type="button" class="btn-clear" onclick="clearAdvancedForm()">
                        <span class="btn-text">Limpar</span>
                        <span class="btn-icon">🗑️</span>
                    </button>
                    <a href="index.php" class="btn-back">
                        <span class="btn-text">Voltar</span>
                        <span class="btn-icon">←</span>
                    </a>
                </div>
            </form>

            <!-- Seção de Informações -->
            <div class="info-section">
                <h3>ℹ️ Como Funciona a Análise Avançada?</h3>
                <div class="info-cards">
                    <div class="info-card">
                        <h4>🔍 Verificação de Plágio</h4>
                        <p>Busca por conteúdo similar em bases de dados online e websites públicos</p>
                    </div>
                    <div class="info-card">
                        <h4>🤖 Detecção de IA</h4>
                        <p>Analisa padrões linguísticos para identificar texto gerado por inteligência artificial</p>
                    </div>
                    <div class="info-card">
                        <h4>📚 Validação de Referências</h4>
                        <p>Verifica se as referências citadas existem e são acessíveis online</p>
                    </div>
                    <div class="info-card">
                        <h4>📊 Análise Completa</h4>
                        <p>Combina múltiplas técnicas de análise para uma avaliação abrangente</p>
                    </div>
                </div>
            </div>
        </main>

        <footer class="footer">
            <p>&copy; 2024 Sistema de Análise Avançada de Textos - Desenvolvido para TCC</p>
        </footer>
    </div>

    <script>
        // Contador de caracteres
        function updateCharCounter() {
            const textarea = document.getElementById('analysisText');
            const counter = document.getElementById('text-counter');
            
            textarea.addEventListener('input', function() {
                const length = this.value.length;
                counter.textContent = length.toLocaleString();
                
                if (length > 90000) {
                    counter.style.color = '#dc3545';
                } else if (length > 80000) {
                    counter.style.color = '#ffc107';
                } else {
                    counter.style.color = '#6c757d';
                }
            });
        }

        // Alternar entre abas de análise
        function switchAnalysisTab(tabName) {
            // Ocultar todas as abas
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remover classe active de todos os botões
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Mostrar aba selecionada
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Ativar botão selecionado
            event.target.classList.add('active');
        }

        // Configurar upload de arquivo
        function setupFileUpload() {
            const area = document.getElementById('file-upload-area');
            const input = area.querySelector('input[type="file"]');
            const content = area.querySelector('.file-upload-content');
            const selected = area.querySelector('.file-selected');
            const fileName = area.querySelector('.file-name');
            const removeBtn = area.querySelector('.remove-file');
            
            // Clique na área
            area.addEventListener('click', () => {
                input.click();
            });
            
            // Drag and drop
            area.addEventListener('dragover', (e) => {
                e.preventDefault();
                area.classList.add('drag-over');
            });
            
            area.addEventListener('dragleave', () => {
                area.classList.remove('drag-over');
            });
            
            area.addEventListener('drop', (e) => {
                e.preventDefault();
                area.classList.remove('drag-over');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    input.files = files;
                    handleFileSelect(input);
                }
            });
            
            // Seleção de arquivo
            input.addEventListener('change', () => {
                handleFileSelect(input);
            });
            
            // Remover arquivo
            removeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                input.value = '';
                content.style.display = 'block';
                selected.style.display = 'none';
            });
        }

        // Lidar com seleção de arquivo
        function handleFileSelect(input) {
            const file = input.files[0];
            if (!file) return;
            
            // Validar tipo de arquivo
            const allowedTypes = ['.txt', '.md', '.html', '.htm', '.css', '.js', '.json', '.xml', '.csv', '.log', '.pdf', '.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx'];
            const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
            
            if (!allowedTypes.includes(fileExtension)) {
                alert('Formatos aceitos: .txt, .md, .html, .css, .js, .json, .xml, .csv, .log, .pdf, .doc, .docx, .xls, .xlsx, .ppt, .pptx');
                input.value = '';
                return;
            }
            
            // Validar tamanho (10MB)
            if (file.size > 10 * 1024 * 1024) {
                alert('Arquivo muito grande. Máximo: 10MB.');
                input.value = '';
                return;
            }
            
            const area = input.closest('.file-upload-area');
            const content = area.querySelector('.file-upload-content');
            const selected = area.querySelector('.file-selected');
            const fileName = area.querySelector('.file-name');
            
            fileName.textContent = file.name;
            content.style.display = 'none';
            selected.style.display = 'flex';
        }

        // Atualizar valor do threshold
        function updateThreshold() {
            const slider = document.getElementById('similarityThreshold');
            const value = document.querySelector('.threshold-value');
            
            slider.addEventListener('input', function() {
                value.textContent = Math.round(this.value * 100) + '%';
            });
        }

        // Limpar formulário avançado
        function clearAdvancedForm() {
            document.getElementById('analysisText').value = '';
            document.getElementById('text-counter').textContent = '0';
            document.getElementById('analysisUrl').value = '';
            
            // Resetar upload
            const input = document.getElementById('analysisFile');
            input.value = '';
            const content = document.querySelector('.file-upload-content');
            const selected = document.querySelector('.file-selected');
            content.style.display = 'block';
            selected.style.display = 'none';
            
            // Resetar checkboxes
            document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                if (cb.name === 'plagiarismCheck' || cb.name === 'aiDetection' || cb.name === 'referenceValidation') {
                    cb.checked = true;
                } else {
                    cb.checked = false;
                }
            });
            
            // Resetar configurações
            document.getElementById('searchDepth').value = 'standard';
            document.getElementById('language').value = 'pt';
            document.getElementById('similarityThreshold').value = '0.7';
            document.querySelector('.threshold-value').textContent = '70%';
        }

        // Validação do formulário
        function validateAdvancedForm() {
            const isUploadTab = document.getElementById('upload-tab').classList.contains('active');
            const isTextTab = document.getElementById('text-tab').classList.contains('active');
            const isUrlTab = document.getElementById('url-tab').classList.contains('active');
            
            if (isUploadTab) {
                const file = document.getElementById('analysisFile').files[0];
                if (!file) {
                    alert('Selecione um arquivo para análise.');
                    return false;
                }
            } else if (isTextTab) {
                const text = document.getElementById('analysisText').value.trim();
                if (text.length < 50) {
                    alert('O texto deve ter pelo menos 50 caracteres.');
                    return false;
                }
            } else if (isUrlTab) {
                const url = document.getElementById('analysisUrl').value.trim();
                if (!url) {
                    alert('Digite uma URL válida.');
                    return false;
                }
                try {
                    new URL(url);
                } catch {
                    alert('Digite uma URL válida.');
                    return false;
                }
            }
            
            return true;
        }

        // Inicializar quando a página carregar
        document.addEventListener('DOMContentLoaded', function() {
            updateCharCounter();
            setupFileUpload();
            updateThreshold();
            
            // Adicionar validação ao formulário
            document.querySelector('.advanced-form').addEventListener('submit', function(e) {
                if (!validateAdvancedForm()) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
