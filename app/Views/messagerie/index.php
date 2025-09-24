<?php
$title = "Mes Messages - EcoRide";
$additionalCSS = [
    '/css/messagerie.css'
];

ob_start();
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-comments text-primary"></i> Mes Messages</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newConversationModal">
                    <i class="fas fa-plus"></i> Nouvelle conversation
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Liste des conversations -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-inbox"></i> Conversations</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($conversationsFormatees)): ?>
                        <div class="text-center p-4 text-muted">
                            <i class="fas fa-comments fa-3x mb-3"></i>
                            <p>Aucune conversation pour le moment</p>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newConversationModal">
                                Commencer une conversation
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($conversationsFormatees as $conversation): ?>
                                <a href="/messages/conversation/<?= $conversation['id'] ?>" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <?php foreach ($conversation['participants'] as $participant): ?>
                                                    <?php 
                                                    // ✅ GESTION COMPATIBLE OBJET/ARRAY
                                                    $participantUserId = is_object($participant) ? $participant->user_id : $participant['user_id'];
                                                    $participantPseudo = is_object($participant) ? $participant->pseudo : $participant['pseudo'];
                                                    
                                                    if ($participantUserId !== $_SESSION['user_id']): 
                                                    ?>
                                                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($participantPseudo) ?>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i> 
                                                <?= $conversation['derniere_activite']->format('d/m/Y H:i') ?>
                                            </small>
                                        </div>
                                        <?php 
                                        $nonLus = $conversation['messages_non_lus'][$_SESSION['user_id']] ?? 0;
                                        if ($nonLus > 0): 
                                        ?>
                                            <span class="badge bg-danger rounded-pill"><?= $nonLus ?></span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Zone de sélection -->
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-center text-muted">
                    <div class="text-center">
                        <i class="fas fa-comments fa-4x mb-3"></i>
                        <h4>Sélectionnez une conversation</h4>
                        <p>Choisissez une conversation dans la liste de gauche pour commencer à discuter</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal nouvelle conversation avec autocomplétion -->
<div class="modal fade" id="newConversationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle"></i> Nouvelle conversation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newConversationForm">
                    <!-- Champ destinataire avec autocomplétion -->
                    <div class="mb-3">
                        <label for="destinataireInput" class="form-label">
                            <i class="fas fa-user"></i> Destinataire
                        </label>
                        <div class="autocomplete-container">
                            <input type="text" 
                                   class="form-control" 
                                   id="destinataireInput" 
                                   placeholder="Tapez le pseudo du destinataire..." 
                                   autocomplete="off"
                                   required>
                            <div id="userSuggestions" class="suggestions-dropdown"></div>
                        </div>
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i> Commencez à taper pour voir les suggestions
                        </div>
                        <input type="hidden" id="destinataireId" name="destinataire_id">
                    </div>

                    <!-- Motif de contact -->
                    <div class="mb-3">
                        <label for="motifSelect" class="form-label">
                            <i class="fas fa-tag"></i> Motif de contact
                        </label>
                        <select class="form-select" id="motifSelect" required>
                            <option value="">Sélectionnez un motif...</option>
                        </select>
                        <div class="form-text motif-description" id="motifDescription">
                            Choisissez le motif principal de votre message
                        </div>
                    </div>

                    <!-- Message initial optionnel -->
                    <div class="mb-3">
                        <label for="messageInitial" class="form-label">
                            <i class="fas fa-comment"></i> Premier message (optionnel)
                        </label>
                        <textarea class="form-control" 
                                  id="messageInitial" 
                                  rows="3" 
                                  maxlength="500"
                                  placeholder="Écrivez votre premier message..."></textarea>
                        <div class="form-text">
                            <span id="messageCount" class="char-counter">0</span>/500 caractères
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <button type="button" class="btn btn-primary" id="startConversationBtn">
                    <i class="fas fa-paper-plane"></i> Commencer la conversation
                </button>
            </div>
        </div>
    </div>
</div>

<script src="/js/messagerie.js"></script>
<?php

$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
