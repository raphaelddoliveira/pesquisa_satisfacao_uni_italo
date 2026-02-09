<?php
// Processamento do formulário
$mensagem = '';
$tipo_mensagem = '';
$form_enviado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura os dados do formulário
    $telefone = isset($_GET['telefone']) ? htmlspecialchars($_GET['telefone']) : '';
    $duvida = isset($_POST['duvida']) ? htmlspecialchars($_POST['duvida']) : '';
    $avalia_atendimento = isset($_POST['avalia_atendimento']) ? htmlspecialchars($_POST['avalia_atendimento']) : '';
    $recomendacao = isset($_POST['recomendacao']) ? htmlspecialchars($_POST['recomendacao']) : '';
    $comentario = isset($_POST['comentario']) ? htmlspecialchars($_POST['comentario']) : '';

    // Validação
    if (empty($duvida) || empty($avalia_atendimento) || $recomendacao === '') {
        $mensagem = 'Por favor, preencha todas as perguntas obrigatórias.';
        $tipo_mensagem = 'erro';
    } else {
        // Prepara os dados para enviar ao webhook
        $dados = array(
            'telefone' => $telefone,
            'duvida' => $duvida,
            'avalia_atendimento' => $avalia_atendimento,
            'recomendacao' => $recomendacao,
            'comentario' => $comentario
        );

        // Envia para o webhook
        $url = 'https://n8n.automacao1.uniitalo.com.br/webhook/pesquisa_satisfacao';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $form_enviado = true;
        } else {
            $mensagem = 'Ocorreu um erro ao enviar sua resposta. Por favor, tente novamente.';
            $tipo_mensagem = 'erro';
        }
    }
}

// Captura telefone da URL para exibição
$telefone_url = isset($_GET['telefone']) ? htmlspecialchars($_GET['telefone']) : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisa de Satisfação - Uni Italo</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #1a3a2a 0%, #0d1f15 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
            margin-top: 20px;
        }

        .header {
            background: linear-gradient(135deg, #1a3a2a 0%, #2d5a3d 100%);
            padding: 30px;
            text-align: center;
            border-bottom: 4px solid #c41e3a;
        }

        .logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 15px;
        }

        .header h1 {
            color: #ffffff;
            font-size: 24px;
            font-weight: 500;
            margin-top: 10px;
        }

        .header p {
            color: #d4af37;
            font-size: 14px;
            margin-top: 5px;
        }

        .form-content {
            padding: 30px;
        }

        .question {
            margin-bottom: 30px;
        }

        .question-title {
            color: #1a3a2a;
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 15px;
            padding-left: 10px;
            border-left: 3px solid #c41e3a;
        }

        .question-number {
            color: #c41e3a;
            font-weight: 700;
        }

        /* Radio buttons customizados */
        .radio-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .radio-option {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            background: #f8f9fa;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .radio-option:hover {
            background: #e8f5e9;
            border-color: #2d5a3d;
        }

        .radio-option input[type="radio"] {
            display: none;
        }

        .radio-option .radio-custom {
            width: 20px;
            height: 20px;
            border: 2px solid #1a3a2a;
            border-radius: 50%;
            margin-right: 12px;
            position: relative;
            transition: all 0.3s ease;
        }

        .radio-option input[type="radio"]:checked + .radio-custom {
            border-color: #c41e3a;
            background: #c41e3a;
        }

        .radio-option input[type="radio"]:checked + .radio-custom::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 8px;
            height: 8px;
            background: #ffffff;
            border-radius: 50%;
        }

        .radio-option input[type="radio"]:checked ~ .radio-label {
            color: #c41e3a;
            font-weight: 500;
        }

        .radio-label {
            color: #333;
            font-size: 14px;
        }

        /* Rating stars */
        .rating-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .stars-wrapper {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .star-btn {
            width: 45px;
            height: 45px;
            border: 2px solid #d4af37;
            background: #fff;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: #1a3a2a;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .star-btn:hover {
            background: #d4af37;
            color: #fff;
            transform: scale(1.1);
        }

        .star-btn.selected {
            background: #d4af37;
            color: #fff;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.4);
        }

        .rating-label {
            display: flex;
            justify-content: space-between;
            width: 100%;
            font-size: 12px;
            color: #666;
            padding: 0 10px;
        }

        .selected-rating {
            font-size: 16px;
            color: #1a3a2a;
            font-weight: 500;
            min-height: 24px;
        }

        /* Textarea */
        .comment-box {
            width: 100%;
            min-height: 120px;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
            resize: vertical;
            transition: border-color 0.3s ease;
        }

        .comment-box:focus {
            outline: none;
            border-color: #1a3a2a;
        }

        .comment-box::placeholder {
            color: #999;
        }

        /* Submit button */
        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #c41e3a 0%, #a01830 100%);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 20px;
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, #a01830 0%, #8a1428 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(196, 30, 58, 0.4);
        }

        .submit-btn:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
        }

        .footer p {
            color: #666;
            font-size: 12px;
        }

        /* Success message */
        .success-message {
            display: none;
            text-align: center;
            padding: 60px 30px;
        }

        .success-message.show {
            display: block;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #2d5a3d 0%, #1a3a2a 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .success-icon svg {
            width: 40px;
            height: 40px;
            fill: #ffffff;
        }

        .success-message h2 {
            color: #1a3a2a;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .success-message p {
            color: #666;
            font-size: 14px;
        }

        /* Error message */
        .error-message {
            background: #ffebee;
            color: #c41e3a;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 20px 30px 0;
            font-size: 14px;
        }

        /* Hide form when success */
        .form-content.hide {
            display: none;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .container {
                margin: 10px;
            }

            .header {
                padding: 20px;
            }

            .form-content {
                padding: 20px;
            }

            .star-btn {
                width: 38px;
                height: 38px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="images/logo (1).svg" alt="Uni Italo" class="logo">
            <h1>Pesquisa de Satisfação</h1>
            <p>Sua opinião é muito importante para nós!</p>
        </div>

        <?php if ($tipo_mensagem === 'erro'): ?>
            <div class="error-message">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <?php if ($form_enviado): ?>
            <div class="success-message show">
                <div class="success-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                    </svg>
                </div>
                <h2>Obrigado!</h2>
                <p>Sua resposta foi enviada com sucesso.<br>Agradecemos sua participação!</p>
            </div>
        <?php else: ?>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" class="form-content">
                <!-- Pergunta 01 -->
                <div class="question">
                    <h3 class="question-title">
                        <span class="question-number">Pergunta 01:</span><br>
                        O seu problema ou dúvida foi resolvido de forma satisfatória?
                    </h3>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="duvida" value="Sim" required>
                            <span class="radio-custom"></span>
                            <span class="radio-label">Sim</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="duvida" value="Não">
                            <span class="radio-custom"></span>
                            <span class="radio-label">Não</span>
                        </label>
                    </div>
                </div>

                <!-- Pergunta 02 -->
                <div class="question">
                    <h3 class="question-title">
                        <span class="question-number">Pergunta 02:</span><br>
                        Como você avalia o atendimento em relação ao seu questionamento?
                    </h3>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="avalia_atendimento" value="Ótimo" required>
                            <span class="radio-custom"></span>
                            <span class="radio-label">Ótimo</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="avalia_atendimento" value="Bom">
                            <span class="radio-custom"></span>
                            <span class="radio-label">Bom</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="avalia_atendimento" value="Regular">
                            <span class="radio-custom"></span>
                            <span class="radio-label">Regular</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="avalia_atendimento" value="Ruim">
                            <span class="radio-custom"></span>
                            <span class="radio-label">Ruim</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="avalia_atendimento" value="Péssimo">
                            <span class="radio-custom"></span>
                            <span class="radio-label">Péssimo</span>
                        </label>
                    </div>
                </div>

                <!-- Pergunta 03 -->
                <div class="question">
                    <h3 class="question-title">
                        <span class="question-number">Pergunta 03:</span><br>
                        Em uma escala de 0 a 10, qual a probabilidade de você recomendar a Ítalo a um amigo, colega ou familiar?
                    </h3>
                    <div class="rating-container">
                        <div class="stars-wrapper">
                            <?php for ($i = 0; $i <= 10; $i++): ?>
                                <button type="button" class="star-btn" data-value="<?php echo $i; ?>"><?php echo $i; ?></button>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="recomendacao" id="recomendacao" required>
                        <div class="rating-label">
                            <span>Pouco provável</span>
                            <span>Muito provável</span>
                        </div>
                        <div class="selected-rating" id="selectedRating"></div>
                    </div>
                </div>

                <!-- Campo aberto -->
                <div class="question">
                    <h3 class="question-title">
                        <span class="question-number">Campo aberto:</span><br>
                        Deixe-nos um comentário: Elogio, sugestão ou reclamação.
                    </h3>
                    <textarea class="comment-box" name="comentario" id="comentario"
                              placeholder="Digite seu comentário aqui... (opcional)"></textarea>
                </div>

                <button type="submit" class="submit-btn">Enviar Pesquisa</button>
            </form>
        <?php endif; ?>

        <div class="footer">
            <p>&copy; 2025 Uni Italo - Todos os direitos reservados</p>
        </div>
    </div>

    <script>
        // Rating buttons
        const starBtns = document.querySelectorAll('.star-btn');
        const recomendacaoInput = document.getElementById('recomendacao');
        const selectedRatingText = document.getElementById('selectedRating');

        if (starBtns.length > 0) {
            starBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');

                    // Remove selected class from all buttons
                    starBtns.forEach(b => b.classList.remove('selected'));

                    // Add selected class to clicked button
                    this.classList.add('selected');

                    // Update hidden input
                    recomendacaoInput.value = value;

                    // Update text
                    selectedRatingText.textContent = `Você selecionou: ${value}`;
                });
            });
        }

        // Validação antes do submit
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!recomendacaoInput.value) {
                    e.preventDefault();
                    alert('Por favor, selecione uma nota de 0 a 10 na Pergunta 03.');
                }
            });
        }
    </script>
</body>
</html>
