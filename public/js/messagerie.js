/**
 * MESSAGERIE ECORIDE - FICHIER JS EXTERNE
 * Je gère la création et la gestion des conversations via messagerie
 */

// PARTIE 1 : CLASSE POUR CRÉER UNE NOUVELLE CONVERSATION
class NewConversationManager {
    // Je crée le constructeur (la fonction spéciale qui lance au démarrage)
    constructor() {
        this.selectedUser = null;        // Je stocke l'utilisateur sélectionné
        this.searchTimeout = null;       // Je stocke le timer du debounce
        this.initEventListeners();       // J'initialise tous les écouteurs d'événements
        this.loadMotifs();               // Je charge les motifs de conversation depuis le serveur
    }
    
    // Je configure tous les événements de la page
    initEventListeners() {
        // Je récupère tous les éléments HTML du DOM (Direct Object Model = la page)
        const input = document.getElementById('destinataireInput');          // Champ de saisie destinataire
        const motifSelect = document.getElementById('motifSelect');          // Dropdown des motifs
        const messageArea = document.getElementById('messageInitial');       // Zone de texte du message
        const startBtn = document.getElementById('startConversationBtn');    // Bouton démarrer conversation
        
        // Je fais un log pour déboguer (affiche dans la console)
        console.log('Éléments trouvés:', { input, motifSelect, messageArea, startBtn });
        
        // Si le champ de saisie existe, je l'écoute
        if (input) {
            input.addEventListener('input', (e) => {
                console.log('Saisie:', e.target.value); // Je log la saisie
                this.handleSearchInput(e.target.value.trim()); // J'appelle la fonction de recherche
            });
        }
        
        // Si le bouton "Démarrer conversation" existe, je l'écoute
        if (startBtn) {
            startBtn.addEventListener('click', () => {
                console.log('Clic démarrer conversation'); // Je log le clic
                this.startConversation(); // J'appelle la fonction pour démarrer
            });
        }
        
        // Si la zone de message existe, je mets à jour le compteur de caractères
        if (messageArea) {
            messageArea.addEventListener('input', (e) => {
                this.updateCharacterCount(e.target.value.length); // Je compte les caractères saisis
            });
        }
    }
    
    // Je traite la saisie utilisateur (avec debounce pour optimiser)
    handleSearchInput(query) {
        clearTimeout(this.searchTimeout);  // Je vide le timer précédent
        
        // Si la query < 2 caractères, c'est trop court, je la cache
        if (query.length < 2) {
            this.hideSuggestions();        // Je masque les suggestions
            this.selectedUser = null;      // Je réinitialise l'utilisateur sélectionné
            return;                        // Je quitte la fonction
        }
        
        // Je DOIS ATTENDRE 300ms avant de chercher (debounce = anti-spam)
        this.searchTimeout = setTimeout(() => {
            this.searchUsers(query);       // Après 300ms, je lance la recherche
        }, 300);
    }
    
    // J'utilise async pour faire une requête non-bloquante au serveur
    async searchUsers(query) {
        try {
            console.log('Recherche utilisateurs:', query); // Je log la recherche
            
            // J'attends la réponse du serveur avec fetch (requête HTTP)
            const response = await fetch(`/api/users/search?q=${encodeURIComponent(query)}`);
            
            // J'attends la conversion en JSON
            const data = await response.json();
            
            // Je log les résultats trouvés
            console.log('Utilisateurs trouvés:', data);
            
            // Si des utilisateurs sont trouvés
            if (data.users && data.users.length > 0) {
                this.showSuggestions(data.users);  // Je les affiche en suggestions
            } else {
                this.showNoResults();              // Sinon, j'affiche "Aucun résultat"
            }
        } catch (error) {
            // Si erreur réseau, je la log
            console.error('Erreur recherche:', error);
        }
    }
    
    // Je génère et affiche la liste des suggestions (utilisateurs trouvés)
    showSuggestions(users) {
        const container = document.getElementById('userSuggestions');
        if (!container) return; // Je quitte si le conteneur n'existe pas
        
        // Je crée du HTML pour chaque utilisateur trouvé
        container.innerHTML = users.map(user => `
            <div class="suggestion-item p-2 border-bottom" data-user-id="${user.id}" data-pseudo="${user.pseudo}" style="cursor: pointer;">
                <div class="fw-bold">${user.pseudo}</div>
                ${user.nom_complet ? `<small class="text-muted">${user.nom_complet}</small>` : ''}
            </div>
        `).join('');
        
        // Je rends cliquable chaque suggestion
        container.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', () => {
                // Quand je clique, je récupère les données de cet utilisateur
                this.selectUser({
                    id: item.getAttribute('data-user-id'),
                    pseudo: item.getAttribute('data-pseudo')
                });
            });
        });
        
        // Je montre le conteneur (display: block)
        container.style.display = 'block';
    }
    
    // J'affiche "Aucun utilisateur trouvé" si la recherche est vide
    showNoResults() {
        const container = document.getElementById('userSuggestions');
        if (container) {
            container.innerHTML = '<div class="p-2 text-muted">Aucun utilisateur trouvé</div>';
            container.style.display = 'block';
        }
    }
    
    // J'efface les suggestions de l'écran
    hideSuggestions() {
        const container = document.getElementById('userSuggestions');
        if (container) {
            container.style.display = 'none'; // Je cache le conteneur
        }
    }
    
    // Je sélectionne un utilisateur après qu'il ait cliqué sur une suggestion
    selectUser(user) {
        console.log('Utilisateur sélectionné:', user); // Je log la sélection
        
        this.selectedUser = user;                              // Je sauvegarde l'utilisateur
        document.getElementById('destinataireInput').value = user.pseudo;  // Je remplis le champ de saisie
        document.getElementById('destinataireId').value = user.id;         // Je remplis l'ID caché
        this.hideSuggestions();                                            // Je masque les suggestions
    }
    
    // J'utilise async pour charger les motifs de conversation depuis le serveur
    async loadMotifs() {
        try {
            console.log('Chargement des motifs...');
            
            // J'attends la réponse du serveur
            const response = await fetch('/api/messages/motifs');
            
            // J'attends la conversion JSON
            const data = await response.json();
            
            // Je récupère le dropdown des motifs
            const motifSelect = document.getElementById('motifSelect');
            
            // Si le dropdown existe ET j'ai des motifs
            if (motifSelect && data.motifs) {
                // Je vide le dropdown
                motifSelect.innerHTML = '<option value="">Sélectionnez un motif...</option>';
                
                // Je boucle sur chaque motif et je crée une option
                data.motifs.forEach(motif => {
                    const option = document.createElement('option');  // Je crée une nouvelle option
                    option.value = motif.id;                          // Je lui donne la valeur
                    option.textContent = motif.libelle;               // Je lui donne le label
                    motifSelect.appendChild(option);                  // Je l'ajoute au dropdown
                });
                
                console.log('Motifs chargés !'); // Je log le succès
            }
        } catch (error) {
            console.error('Erreur motifs:', error); // Je log l'erreur
        }
    }
    
    // Je mets à jour le compteur de caractères en temps réel
    updateCharacterCount(length) {
        const counter = document.getElementById('messageCount');
        if (counter) {
            counter.textContent = length; // Je mets le nombre dans le HTML
        }
    }
    
    // J'utilise async pour démarrer une nouvelle conversation
    async startConversation() {
        console.log('Démarrage conversation...');
        
        // Si pas d'utilisateur sélectionné, j'alerte
        if (!this.selectedUser) {
            alert('Veuillez sélectionner un destinataire');
            return;
        }
        
        // Je récupère les valeurs du formulaire
        const motif = document.getElementById('motifSelect').value;
        const messageInitial = document.getElementById('messageInitial').value.trim();
        
        // Si pas de motif, j'alerte
        if (!motif) {
            alert('Veuillez sélectionner un motif');
            return;
        }
        
        try {
            console.log('Envoi conversation...');
            
            // J'attends la réponse POST au serveur
            const response = await fetch('/api/messages/new', {
                method: 'POST',                                    // Je fais une requête POST (créer)
                headers: { 'Content-Type': 'application/json' },  // Je dis que c'est du JSON
                body: JSON.stringify({                             // Je crée l'objet à envoyer
                    destinataire_id: this.selectedUser.id,
                    destinataire_pseudo: this.selectedUser.pseudo,
                    motif: motif,
                    message_initial: messageInitial
                })
            });
            
            // J'attends la conversion JSON
            const result = await response.json();
            console.log('Résultat:', result); // Je log la réponse du serveur
            
            // Si la création est réussie
            if (result.success) {
                alert('Conversation créée !');
                window.location.href = result.redirect; // Je redirige vers la conversation
            } else {
                alert('Erreur : ' + result.error); // Sinon, j'affiche l'erreur
            }
        } catch (error) {
            console.error('Erreur:', error);
            alert('Erreur : ' + error.message); // Erreur réseau
        }
    }
}

// J'initialise la messagerie quand la page est chargée
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initialisation messagerie depuis fichier externe');
    window.conversationManager = new NewConversationManager(); // Je crée une instance de la classe
});


// ===================================================================
// PARTIE 2 : CLASSE POUR GÉRER UNE CONVERSATION EXISTANTE
// ===================================================================

class ConversationManager {
    constructor() {
        this.conversationId = null;  // Je stocke l'ID de la conversation
        this.initConversation();     // J'initialise
    }
    
    // Je récupère l'ID de conversation depuis la page HTML
    initConversation() {
        const conversationIdInput = document.getElementById('conversationId');
        if (conversationIdInput) {
            this.conversationId = conversationIdInput.value;
            console.log('Conversation ID:', this.conversationId);
            this.initEventListeners(); // J'initialise les écouteurs
        }
    }
    
    // Je configure les écouteurs d'événements du formulaire de message
    initEventListeners() {
        const messageForm = document.getElementById('messageForm');
        const messageInput = document.getElementById('messageInput');
        
        // Si le formulaire existe
        if (messageForm) {
            messageForm.addEventListener('submit', (e) => {
                e.preventDefault();        // J'empêche la soumission normale (rechargement)
                this.sendMessage();        // Je déclenche ma fonction d'envoi
            });
        }
        
        // Si le champ de message existe
        if (messageInput) {
            messageInput.addEventListener('input', (e) => {
                // Je compte les caractères tapés
                const count = e.target.value.length;
                const counter = document.getElementById('charCount');
                if (counter) counter.textContent = count; // Je mets à jour le compteur
            });
        }
    }
    
    // J'utilise async pour envoyer un message
    async sendMessage() {
        const messageInput = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');
        
        // Si pas d'éléments, je quitte
        if (!messageInput || !this.conversationId) {
            console.error('Éléments manquants');
            return;
        }
        
        // Je récupère le contenu du message
        const contenu = messageInput.value.trim();
        if (!contenu) {
            alert('Veuillez saisir un message');
            return;
        }
        
        // Je désactive le bouton pendant l'envoi (UX design)
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...'; // Spinner d'animation
        
        try {
            console.log('Envoi message:', { conversationId: this.conversationId, contenu });
            
            // J'attends la réponse POST du serveur
            const response = await fetch('/api/messages/send', {
                method: 'POST',                                    // Requête POST (créer un message)
                headers: { 'Content-Type': 'application/json' },  // Format JSON
                body: JSON.stringify({                             // Corps de la requête
                    conversation_id: this.conversationId,
                    contenu: contenu
                })
            });
            
            // J'attends la conversion JSON
            const result = await response.json();
            console.log('Résultat envoi:', result); // Je log la réponse
            
            // Si envoi réussi
            if (result.success) {
                messageInput.value = '';                           // Je vide le champ
                document.getElementById('charCount').textContent = '0'; // Je réinitialise le compteur
                window.location.href = '/messages';               // Je recharge la page
            } else {
                alert('Erreur : ' + (result.error || 'Envoi échoué')); // Je montre l'erreur
            }
            
        } catch (error) {
            console.error('Erreur envoi:', error);
            alert('Erreur : ' + error.message); // Erreur réseau
        } finally {
            // Je réactive le bouton TOUJOURS (succès ou erreur)
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i><span class="d-none d-md-inline ms-1">Envoyer</span>';
        }
    }
}

// J'initialise le gestionnaire de conversation si on est sur une page de conversation
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('conversationId')) {
        console.log('Initialisation gestionnaire conversation');
        window.conversationManager = new ConversationManager(); // Je crée une instance
    }
});
