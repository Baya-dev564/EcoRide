/**
 * MESSAGERIE ECORIDE - FICHIER JS EXTERNE
 */

class NewConversationManager {
    constructor() {
        this.selectedUser = null;
        this.searchTimeout = null;
        this.initEventListeners();
        this.loadMotifs();
    }
    
    initEventListeners() {
        const input = document.getElementById('destinataireInput');
        const motifSelect = document.getElementById('motifSelect');
        const messageArea = document.getElementById('messageInitial');
        const startBtn = document.getElementById('startConversationBtn');
        
        console.log('🔍 Éléments trouvés:', { input, motifSelect, messageArea, startBtn });
        
        if (input) {
            input.addEventListener('input', (e) => {
                console.log('🔄 Saisie:', e.target.value);
                this.handleSearchInput(e.target.value.trim());
            });
        }
        
        if (startBtn) {
            startBtn.addEventListener('click', () => {
                console.log('🚀 Clic démarrer conversation');
                this.startConversation();
            });
        }
        
        if (messageArea) {
            messageArea.addEventListener('input', (e) => {
                this.updateCharacterCount(e.target.value.length);
            });
        }
    }
    
    handleSearchInput(query) {
        clearTimeout(this.searchTimeout);
        
        if (query.length < 2) {
            this.hideSuggestions();
            this.selectedUser = null;
            return;
        }
        
        this.searchTimeout = setTimeout(() => {
            this.searchUsers(query);
        }, 300);
    }
    
    async searchUsers(query) {
        try {
            console.log('🌐 Recherche utilisateurs:', query);
            const response = await fetch(`/api/users/search?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            console.log('👥 Utilisateurs trouvés:', data);
            
            if (data.users && data.users.length > 0) {
                this.showSuggestions(data.users);
            } else {
                this.showNoResults();
            }
        } catch (error) {
            console.error('❌ Erreur recherche:', error);
        }
    }
    
    showSuggestions(users) {
        const container = document.getElementById('userSuggestions');
        if (!container) return;
        
        container.innerHTML = users.map(user => `
            <div class="suggestion-item p-2 border-bottom" data-user-id="${user.id}" data-pseudo="${user.pseudo}" style="cursor: pointer;">
                <div class="fw-bold">${user.pseudo}</div>
                ${user.nom_complet ? `<small class="text-muted">${user.nom_complet}</small>` : ''}
            </div>
        `).join('');
        
        container.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', () => {
                this.selectUser({
                    id: item.getAttribute('data-user-id'),
                    pseudo: item.getAttribute('data-pseudo')
                });
            });
        });
        
        container.style.display = 'block';
    }
    
    showNoResults() {
        const container = document.getElementById('userSuggestions');
        if (container) {
            container.innerHTML = '<div class="p-2 text-muted">❌ Aucun utilisateur trouvé</div>';
            container.style.display = 'block';
        }
    }
    
    hideSuggestions() {
        const container = document.getElementById('userSuggestions');
        if (container) {
            container.style.display = 'none';
        }
    }
    
    selectUser(user) {
        console.log('👤 Utilisateur sélectionné:', user);
        this.selectedUser = user;
        document.getElementById('destinataireInput').value = user.pseudo;
        document.getElementById('destinataireId').value = user.id;
        this.hideSuggestions();
    }
    
    async loadMotifs() {
        try {
            console.log('📋 Chargement des motifs...');
            const response = await fetch('/api/messages/motifs');
            const data = await response.json();
            
            const motifSelect = document.getElementById('motifSelect');
            if (motifSelect && data.motifs) {
                motifSelect.innerHTML = '<option value="">Sélectionnez un motif...</option>';
                data.motifs.forEach(motif => {
                    const option = document.createElement('option');
                    option.value = motif.id;
                    option.textContent = motif.libelle;
                    motifSelect.appendChild(option);
                });
                console.log('✅ Motifs chargés !');
            }
        } catch (error) {
            console.error('❌ Erreur motifs:', error);
        }
    }
    
    updateCharacterCount(length) {
        const counter = document.getElementById('messageCount');
        if (counter) {
            counter.textContent = length;
        }
    }
    
    async startConversation() {
        console.log('🚀 Démarrage conversation...');
        
        if (!this.selectedUser) {
            alert('Veuillez sélectionner un destinataire');
            return;
        }
        
        const motif = document.getElementById('motifSelect').value;
        const messageInitial = document.getElementById('messageInitial').value.trim();
        
        if (!motif) {
            alert('Veuillez sélectionner un motif');
            return;
        }
        
        try {
            const response = await fetch('/api/messages/new', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    destinataire_id: this.selectedUser.id,
                    destinataire_pseudo: this.selectedUser.pseudo,
                    motif: motif,
                    message_initial: messageInitial
                })
            });
            
            const result = await response.json();
            console.log('📤 Résultat:', result);
            
            if (result.success) {
                alert('Conversation créée !');
                window.location.href = result.redirect;
            } else {
                alert('Erreur : ' + result.error);
            }
        } catch (error) {
            console.error('❌ Erreur:', error);
            alert('Erreur : ' + error.message);
        }
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Initialisation messagerie depuis fichier externe');
    window.conversationManager = new NewConversationManager();
});


// =============================================================================
// 💬 GESTION DES MESSAGES DANS UNE CONVERSATION EXISTANTE
// =============================================================================

class ConversationManager {
    constructor() {
        this.conversationId = null;
        this.initConversation();
    }
    
    initConversation() {
        // Je récupère l'ID de conversation depuis la page
        const conversationIdInput = document.getElementById('conversationId');
        if (conversationIdInput) {
            this.conversationId = conversationIdInput.value;
            console.log('💬 Conversation ID:', this.conversationId);
            this.initEventListeners();
        }
    }
    
    initEventListeners() {
        const messageForm = document.getElementById('messageForm');
        const messageInput = document.getElementById('messageInput');
        
        if (messageForm) {
            messageForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.sendMessage();
            });
        }
        
        if (messageInput) {
            // Compteur de caractères
            messageInput.addEventListener('input', (e) => {
                const count = e.target.value.length;
                const counter = document.getElementById('charCount');
                if (counter) counter.textContent = count;
            });
        }
    }
    
    async sendMessage() {
        const messageInput = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');
        
        if (!messageInput || !this.conversationId) {
            console.error('❌ Éléments manquants');
            return;
        }
        
        const contenu = messageInput.value.trim();
        if (!contenu) {
            alert('Veuillez saisir un message');
            return;
        }
        
        // Je désactive le bouton pendant l'envoi
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
        
        try {
            console.log('📤 Envoi message:', { conversationId: this.conversationId, contenu });
            
            const response = await fetch('/api/messages/send', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    conversation_id: this.conversationId,
                    contenu: contenu
                })
            });
            
            const result = await response.json();
            console.log('📨 Résultat envoi:', result);
            
            if (result.success) {
                // Je vide le champ et recharge la page
                messageInput.value = '';
                document.getElementById('charCount').textContent = '0';
                window.location.reload(); // Recharge pour voir le nouveau message
            } else {
                alert('Erreur : ' + (result.error || 'Envoi échoué'));
            }
            
        } catch (error) {
            console.error('❌ Erreur envoi:', error);
            alert('Erreur : ' + error.message);
        } finally {
            // Je réactive le bouton
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i><span class="d-none d-md-inline ms-1">Envoyer</span>';
        }
    }
}

// J'initialise le gestionnaire de conversation
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('conversationId')) {
        console.log('✅ Initialisation gestionnaire conversation');
        window.conversationManager = new ConversationManager();
    }
});
