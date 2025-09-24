/**
 * JavaScript pour la modération des trajets admin
 * Gestion des boutons Valider/Refuser en AJAX
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin trajets JS chargé');
    
    // Je gère les clics sur les boutons de modération
    document.addEventListener('click', function(e) {
        // Bouton Valider (vert)
        if (e.target.closest('.btn-valider')) {
            e.preventDefault();
            const button = e.target.closest('.btn-valider');
            const trajetId = button.getAttribute('data-trajet-id');
            
            if (confirm('Êtes-vous sûr de vouloir valider ce trajet ?')) {
                modererTrajet(trajetId, 'valide');
            }
        }
        
        // Bouton Refuser (rouge)
        if (e.target.closest('.btn-refuser')) {
            e.preventDefault();
            const button = e.target.closest('.btn-refuser');
            const trajetId = button.getAttribute('data-trajet-id');
            
            const motif = prompt('Motif du refus (optionnel) :');
            if (motif !== null) { // L'utilisateur n'a pas annulé
                modererTrajet(trajetId, 'refuse', motif);
            }
        }
      
        // Je gère le bouton détails (oeil) - redirection simple
        if (e.target.closest('.btn-details')) {
            e.preventDefault();
            const button = e.target.closest('.btn-details');
            const trajetId = button.getAttribute('data-trajet-id');
            
            // Je redirige vers la page dédiée
            window.location.href = `/admin/trajets/${trajetId}`;
        }
        
        // Bouton Actualiser
        if (e.target.closest('#refreshStats')) {
            e.preventDefault();
            location.reload();
        }
    });
    
    /**
     * Fonction AJAX pour modérer un trajet
     */
    function modererTrajet(trajetId, decision, motif = '') {
        console.log('Modération trajet:', { trajetId, decision, motif });
        
        // Je désactive temporairement les boutons de cette ligne
        const ligne = document.querySelector(`tr[data-trajet-id="${trajetId}"]`);
        if (ligne) {
            const boutons = ligne.querySelectorAll('.btn');
            boutons.forEach(btn => btn.disabled = true);
        }
        
        // Je prépare les données à envoyer
        const formData = new FormData();
        formData.append('trajet_id', trajetId);
        formData.append('decision', decision);
        if (motif) {
            formData.append('motif', motif);
        }
        
        // Je fais l'appel AJAX à la bonne route
        fetch('/admin/api/moderer-trajet', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Réponse status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Réponse data:', data);
            
            if (data.success || data.succes) {
                // Succès : je mets à jour l'interface
                updateInterface(trajetId, decision);
                showAlert('success', data.message || 'Trajet modéré avec succès !');
                
                // J'actualise les stats
                setTimeout(() => {
                    updateStats(decision);
                }, 500);
            } else {
                // Erreur : j'affiche le message
                showAlert('danger', data.message || data.erreur || 'Erreur lors de la modération');
                
                // Je réactive les boutons
                if (ligne) {
                    const boutons = ligne.querySelectorAll('.btn');
                    boutons.forEach(btn => btn.disabled = false);
                }
            }
        })
        .catch(error => {
            console.error('Erreur AJAX:', error);
            showAlert('danger', 'Erreur technique lors de la modération');
            
            // Je réactive les boutons en cas d'erreur
            if (ligne) {
                const boutons = ligne.querySelectorAll('.btn');
                boutons.forEach(btn => btn.disabled = false);
            }
        });
    }
    
    /**
     * Je mets à jour l'interface après modération
     */
    function updateInterface(trajetId, decision) {
        const ligne = document.querySelector(`tr[data-trajet-id="${trajetId}"]`);
        if (!ligne) return;
        
        // Je mets à jour le badge statut
        const badge = ligne.querySelector('.badge');
        if (badge) {
            if (decision === 'valide') {
                badge.className = 'badge bg-success';
                badge.textContent = 'Validé';
            } else if (decision === 'refuse') {
                badge.className = 'badge bg-danger';
                badge.textContent = 'Refusé';
            }
        }
        
        // Je mets à jour les boutons
        const actionDiv = ligne.querySelector('.btn-group');
        if (actionDiv) {
            if (decision === 'valide') {
                actionDiv.innerHTML = `
                    <span class="text-success small">
                        <i class="fas fa-check me-1"></i>
                        Validé
                    </span>
                `;
            } else if (decision === 'refuse') {
                actionDiv.innerHTML = `
                    <span class="text-danger small">
                        <i class="fas fa-times me-1"></i>
                        Refusé
                    </span>
                `;
            }
        }
    }
    
    /**
     * Je mets à jour les statistiques en haut
     */
    function updateStats(decision) {
        const enAttenteElement = document.querySelector('.stat-warning .stat-value');
        const validesElement = document.querySelector('.stat-success .stat-value');
        const refusesElement = document.querySelector('.stat-danger .stat-value');
        
        if (decision === 'valide') {
            // Je diminue "En attente", j'augmente "Validés"
            if (enAttenteElement) {
                const current = parseInt(enAttenteElement.textContent) || 0;
                enAttenteElement.textContent = Math.max(0, current - 1);
            }
            if (validesElement) {
                const current = parseInt(validesElement.textContent) || 0;
                validesElement.textContent = current + 1;
            }
        } else if (decision === 'refuse') {
            // Je diminue "En attente", j'augmente "Refusés"
            if (enAttenteElement) {
                const current = parseInt(enAttenteElement.textContent) || 0;
                enAttenteElement.textContent = Math.max(0, current - 1);
            }
            if (refusesElement) {
                const current = parseInt(refusesElement.textContent) || 0;
                refusesElement.textContent = current + 1;
            }
        }
    }
    
    /**
     * J'affiche une notification temporaire
     */
    function showAlert(type, message) {
        const alertContainer = document.getElementById('alertContainer') || createAlertContainer();
        
        const alertId = 'alert-' + Date.now();
        const alert = document.createElement('div');
        alert.id = alertId;
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        alertContainer.appendChild(alert);
        
        // Je supprime automatiquement après 5 secondes
        setTimeout(() => {
            const alertElement = document.getElementById(alertId);
            if (alertElement) {
                alertElement.remove();
            }
        }, 5000);
    }
    
    /**
     * Je crée le conteneur des alertes s'il n'existe pas
     */
    function createAlertContainer() {
        let container = document.getElementById('alertContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'alertContainer';
            container.className = 'position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1055';
            document.body.appendChild(container);
        }
        return container;
    }
});
