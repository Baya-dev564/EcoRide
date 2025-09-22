<?php
$title = "Conversation - EcoRide";
ob_start();
?>


<link rel="stylesheet" href="/css/messagerie.css">


<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center mb-4">
                <a href="/messages" class="btn btn-outline-secondary me-3">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <div class="flex-grow-1">
                    <h2 class="mb-0">
                        <i class="fas fa-comments text-primary"></i> 
                        Conversation
                    </h2>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm messagerie-card">
                <!-- Zone des messages -->
                <div class="card-body messagerie-body" id="messagesContainer">
                    <?php if (empty($messagesFormats)): ?>
                        <div class="text-center text-muted p-4">
                            <i class="fas fa-comment-alt fa-3x mb-3"></i>
                            <p>Aucun message dans cette conversation</p>
                            <p class="small">Commencez par envoyer votre premier message !</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messagesFormats as $message): ?>
                            <div class="message-item <?= $message['expediteur']->user_id === $_SESSION['user_id'] ? 'message-sent' : 'message-received' ?>" 
                                 data-message-id="<?= $message['id'] ?>">
                                <div class="message-bubble">
                                    <div class="message-header">
                                        <?php if ($message['expediteur']->user_id !== $_SESSION['user_id']): ?>
                                            <span class="message-author">
                                                <i class="fas fa-user-circle"></i> 
                                                <?= htmlspecialchars($message['expediteur']->pseudo) ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="message-time">
                                            <i class="fas fa-clock"></i> 
                                            <?= $message['created_at']->format('H:i') ?>
                                        </span>
                                    </div>
                                    <div class="message-content">
                                        <?= nl2br(htmlspecialchars($message['contenu'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>


                <!-- Zone de saisie -->
                <div class="card-footer messagerie-footer">
                    <form id="messageForm" class="d-flex align-items-center">
                        <input type="hidden" id="conversationId" value="<?= $conversationIdForView ?? '' ?>">
                        <div class="flex-grow-1 me-2">
                            <textarea 
                                id="messageInput" 
                                class="form-control message-input" 
                                placeholder="Tapez votre message..."
                                rows="1"
                                maxlength="500"
                                required></textarea>
                            <div class="form-text">
                                <span id="charCount">0</span>/500 caractères
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-send" id="sendBtn">
                            <i class="fas fa-paper-plane"></i>
                            <span class="d-none d-md-inline ms-1">Envoyer</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Loading indicator -->
<div id="loadingIndicator" class="d-none">
    <div class="text-center">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Chargement...</span>
        </div>
    </div>
</div>


<script src="/js/messagerie.js"></script>


<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
