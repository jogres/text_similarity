<?php
/**
 * Sistema de Comparação de Conteúdo de Textos
 * Página principal com formulário para inserção de dois textos
 */

// Incluir arquivo de funções
require_once 'functions/similarity.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Comparação de Textos</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Sistema de Comparação de Conteúdo de Textos</h1>
            <p>Compare a similaridade entre dois textos utilizando técnicas de NLP (TF-IDF + Similaridade Cosseno)</p>
        </header>

        <main class="main-content">
            <form action="process.php" method="POST" enctype="multipart/form-data" class="comparison-form">
                <!-- Seção de Entrada de Texto -->
                <div class="input-section">
                    <h3>📝 Entrada de Textos</h3>
                    
                    <div class="input-tabs">
                        <button type="button" class="tab-button active" onclick="switchTab('text')">Digitar Texto</button>
                        <button type="button" class="tab-button" onclick="switchTab('file')">Upload de Arquivo</button>
                    </div>
                    
                    <!-- Aba de Digitação -->
                    <div id="text-tab" class="tab-content active">
                        <div class="form-group">
                            <label for="text1">Primeiro Texto:</label>
                            <textarea 
                                id="text1" 
                                name="text1" 
                                placeholder="Digite ou cole o primeiro texto aqui..."
                                maxlength="50000"
                            ></textarea>
                            <div class="char-counter">
                                <span id="text1-counter">0</span> / 50.000 caracteres
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="text2">Segundo Texto:</label>
                            <textarea 
                                id="text2" 
                                name="text2" 
                                placeholder="Digite ou cole o segundo texto aqui..."
                                maxlength="50000"
                            ></textarea>
                            <div class="char-counter">
                                <span id="text2-counter">0</span> / 50.000 caracteres
                            </div>
                        </div>
                    </div>
                    
                    <!-- Aba de Upload -->
                    <div id="file-tab" class="tab-content">
                        <div class="form-group">
                            <label for="file1">Primeiro Arquivo:</label>
                            <div class="file-upload-area" id="file1-area">
                                <input type="file" id="file1" name="file1" accept=".txt,.md,.html,.htm,.css,.js,.json,.xml,.csv,.log,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" style="display: none;">
                                <div class="file-upload-content">
                                    <span class="file-icon">📄</span>
                                    <p>Clique para selecionar ou arraste um arquivo</p>
                                    <p class="file-info">Formatos: .txt, .md, .html, .css, .js, .json, .xml, .csv, .log, .pdf, .doc, .docx, .xls, .xlsx, .ppt, .pptx (Máximo: 5MB)</p>
                                </div>
                                <div class="file-selected" style="display: none;">
                                    <span class="file-name"></span>
                                    <button type="button" class="remove-file">✕</button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="file2">Segundo Arquivo:</label>
                            <div class="file-upload-area" id="file2-area">
                                <input type="file" id="file2" name="file2" accept=".txt,.md,.html,.htm,.css,.js,.json,.xml,.csv,.log,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" style="display: none;">
                                <div class="file-upload-content">
                                    <span class="file-icon">📄</span>
                                    <p>Clique para selecionar ou arraste um arquivo</p>
                                    <p class="file-info">Formatos: .txt, .md, .html, .css, .js, .json, .xml, .csv, .log, .pdf, .doc, .docx, .xls, .xlsx, .ppt, .pptx (Máximo: 5MB)</p>
                                </div>
                                <div class="file-selected" style="display: none;">
                                    <span class="file-name"></span>
                                    <button type="button" class="remove-file">✕</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Opções Avançadas -->
                <div class="advanced-options">
                    <h3>Opções Avançadas</h3>
                    <div class="options-grid">
                        <div class="option-group">
                            <label for="removeNumbers">
                                <input type="checkbox" id="removeNumbers" name="removeNumbers" value="1">
                                Remover números dos textos
                            </label>
                        </div>
                        
                        <div class="option-group">
                            <label for="tfNormalization">Normalização TF:</label>
                            <select id="tfNormalization" name="tfNormalization">
                                <option value="raw">Padrão (Raw)</option>
                                <option value="log">Logarítmica</option>
                                <option value="max">Por Frequência Máxima</option>
                                <option value="double">Dupla</option>
                            </select>
                        </div>
                        
                        <div class="option-group">
                            <label for="idfFormula">Fórmula IDF:</label>
                            <select id="idfFormula" name="idfFormula">
                                <option value="smooth">Suavizada (Recomendada)</option>
                                <option value="standard">Padrão</option>
                                <option value="probabilistic">Probabilística</option>
                            </select>
                        </div>
                        
                        <div class="option-group">
                            <label for="normalizeTfIdf">
                                <input type="checkbox" id="normalizeTfIdf" name="normalizeTfIdf" value="1">
                                Normalizar vetores TF-IDF
                            </label>
                        </div>
                        
                        <div class="option-group">
                            <label for="detectAI">
                                <input type="checkbox" id="detectAI" name="detectAI" value="1">
                                Detectar texto gerado por IA
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-compare">
                        <span class="btn-text">Comparar Textos</span>
                        <span class="btn-icon">📊</span>
                    </button>
                    <button type="button" class="btn-clear" onclick="clearForm()">
                        <span class="btn-text">Limpar</span>
                        <span class="btn-icon">🗑️</span>
                    </button>
                    <a href="advanced_analysis.php" class="btn-advanced">
                        <span class="btn-text">Análise Avançada</span>
                        <span class="btn-icon">🔍</span>
                    </a>
                </div>
            </form>

            <div class="info-section">
                <h3>Como funciona?</h3>
                <div class="info-cards">
                    <div class="info-card">
                        <h4>🔤 Tokenização</h4>
                        <p>Os textos são divididos em palavras individuais e normalizados</p>
                    </div>
                    <div class="info-card">
                        <h4>📊 TF-IDF</h4>
                        <p>Calcula a frequência e importância de cada termo nos textos</p>
                    </div>
                    <div class="info-card">
                        <h4>📐 Similaridade Cosseno</h4>
                        <p>Mede a similaridade entre os vetores TF-IDF dos textos</p>
                    </div>
                </div>
            </div>
        </main>

        <footer class="footer">
            <p>&copy; 2024 Sistema de Comparação de Textos - Desenvolvido para TCC</p>
        </footer>
    </div>

    <script>
        // Contador de caracteres em tempo real
        function updateCharCounter(textareaId, counterId) {
            const textarea = document.getElementById(textareaId);
            const counter = document.getElementById(counterId);
            
            textarea.addEventListener('input', function() {
                const length = this.value.length;
                counter.textContent = length.toLocaleString();
                
                // Mudar cor baseada no limite
                if (length > 45000) {
                    counter.style.color = '#dc3545';
                } else if (length > 40000) {
                    counter.style.color = '#ffc107';
                } else {
                    counter.style.color = '#6c757d';
                }
            });
        }

        // Função para limpar formulário
        function clearForm() {
            document.getElementById('text1').value = '';
            document.getElementById('text2').value = '';
            document.getElementById('text1-counter').textContent = '0';
            document.getElementById('text2-counter').textContent = '0';
            
            // Resetar opções avançadas
            document.getElementById('removeNumbers').checked = false;
            document.getElementById('normalizeTfIdf').checked = false;
            document.getElementById('tfNormalization').value = 'raw';
            document.getElementById('idfFormula').value = 'smooth';
        }

        // Função para mostrar/ocultar opções avançadas
        function toggleAdvancedOptions() {
            const options = document.querySelector('.advanced-options');
            const button = document.querySelector('.toggle-advanced');
            
            if (options.style.display === 'none') {
                options.style.display = 'block';
                button.textContent = 'Ocultar Opções Avançadas';
            } else {
                options.style.display = 'none';
                button.textContent = 'Mostrar Opções Avançadas';
            }
        }

        // Validação em tempo real
        function validateForm() {
            const text1 = document.getElementById('text1').value.trim();
            const text2 = document.getElementById('text2').value.trim();
            
            if (text1.length < 10 || text2.length < 10) {
                alert('Cada texto deve ter pelo menos 10 caracteres.');
                return false;
            }
            
            return true;
        }

        // Inicializar quando a página carregar
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar contadores de caracteres
            updateCharCounter('text1', 'text1-counter');
            updateCharCounter('text2', 'text2-counter');
            
            // Configurar upload de arquivos
            setupFileUpload();
            
            // Adicionar validação ao formulário
            document.querySelector('.comparison-form').addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                }
            });
            
            // Adicionar botão para mostrar/ocultar opções avançadas
            const formActions = document.querySelector('.form-actions');
            const toggleButton = document.createElement('button');
            toggleButton.type = 'button';
            toggleButton.className = 'btn-toggle-advanced';
            toggleButton.textContent = 'Mostrar Opções Avançadas';
            toggleButton.onclick = toggleAdvancedOptions;
            formActions.insertBefore(toggleButton, formActions.firstChild);
            
            // Ocultar opções avançadas inicialmente
            document.querySelector('.advanced-options').style.display = 'none';
        });

        // Função para alternar entre abas
        function switchTab(tabName) {
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

        // Função para upload de arquivos
        function setupFileUpload() {
            const fileAreas = document.querySelectorAll('.file-upload-area');
            
            fileAreas.forEach(area => {
                const input = area.querySelector('input[type="file"]');
                const content = area.querySelector('.file-upload-content');
                const selected = area.querySelector('.file-selected');
                const fileName = area.querySelector('.file-name');
                const removeBtn = area.querySelector('.remove-file');
                
                // Clique na área de upload
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
            });
        }

        // Função para lidar com seleção de arquivo
        function handleFileSelect(input) {
            const file = input.files[0];
            if (!file) return;
            
            // Validar tipo de arquivo
            const allowedExtensions = ['.txt', '.md', '.html', '.htm', '.css', '.js', '.json', '.xml', '.csv', '.log', '.pdf', '.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx'];
            const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
            
            if (!allowedExtensions.includes(fileExtension)) {
                alert('Formatos aceitos: .txt, .md, .html, .css, .js, .json, .xml, .csv, .log, .pdf, .doc, .docx, .xls, .xlsx, .ppt, .pptx');
                input.value = '';
                return;
            }
            
            // Validar tamanho (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('Arquivo muito grande. Máximo: 5MB.');
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

        // Validação aprimorada do formulário
        function validateForm() {
            const isTextTab = document.getElementById('text-tab').classList.contains('active');
            
            if (isTextTab) {
                const text1 = document.getElementById('text1').value.trim();
                const text2 = document.getElementById('text2').value.trim();
                
                if (text1.length < 10 || text2.length < 10) {
                    alert('Cada texto deve ter pelo menos 10 caracteres.');
                    return false;
                }
            } else {
                const file1 = document.getElementById('file1').files[0];
                const file2 = document.getElementById('file2').files[0];
                
                if (!file1 || !file2) {
                    alert('Selecione dois arquivos .txt para comparação.');
                    return false;
                }
            }
            
            return true;
        }

        // Função para carregar exemplos
        function loadExample(exampleNumber) {
            const examples = {
                1: {
                    text1: "A inteligência artificial está revolucionando a tecnologia moderna. Máquinas podem aprender e tomar decisões baseadas em dados.",
                    text2: "A IA transforma a tecnologia atual. Computadores aprendem e decidem usando informações e algoritmos avançados."
                },
                2: {
                    text1: "Gatos são animais domésticos muito populares como pets. Eles são independentes e carinhosos.",
                    text2: "A matemática é uma ciência exata fundamental para a engenharia e outras áreas do conhecimento."
                },
                3: {
                    text1: "O desenvolvimento sustentável é essencial para o futuro do planeta. Precisamos preservar os recursos naturais.",
                    text2: "A sustentabilidade ambiental é crucial para as próximas gerações. Devemos proteger o meio ambiente."
                }
            };
            
            if (examples[exampleNumber]) {
                // Garantir que estamos na aba de texto
                switchTab('text');
                
                document.getElementById('text1').value = examples[exampleNumber].text1;
                document.getElementById('text2').value = examples[exampleNumber].text2;
                
                // Atualizar contadores
                document.getElementById('text1-counter').textContent = examples[exampleNumber].text1.length;
                document.getElementById('text2-counter').textContent = examples[exampleNumber].text2.length;
            }
        }
    </script>
</body>
</html>
