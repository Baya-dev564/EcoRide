/**
 * JavaScript pour la modération des trajets admin
 * Gestion des boutons Valider/Refuser en AJAX
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin trajets JS chargé');
    
    // ✅ GÉRER LES CLICS SUR LES BOUTONS VALIDER
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
      
        // ✅ GESTION DU BOUTON DÉTAILS (OEIL) - REDIRECTION SIMPLE
if (e.target.closest('.btn-details')) {
    e.preventDefault();
    const button = e.target.closest('.btn-details');
    const trajetId = button.getAttribute('data-trajet-id');
    
    // ✅ REDIRECTION VERS LA PAGE DÉDIÉE
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
        
        // Désactiver temporairement les boutons de cette ligne
        const ligne = document.querySelector(`tr[data-trajet-id="${trajetId}"]`);
        if (ligne) {
            const boutons = ligne.querySelectorAll('.btn');
            boutons.forEach(btn => btn.disabled = true);
        }
        
        // Préparer les données à envoyer
        const formData = new FormData();
        formData.append('trajet_id', trajetId);
        formData.append('decision', decision);
        if (motif) {
            formData.append('motif', motif);
        }
        
        // ✅ APPEL AJAX À LA BONNE ROUTE
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
                // ✅ SUCCÈS : Mettre à jour l'interface
                updateInterface(trajetId, decision);
                showAlert('success', data.message || 'Trajet modéré avec succès !');
                
                // Actualiser les stats
                setTimeout(() => {
                    updateStats(decision);
                }, 500);
            } else {
                // ❌ ERREUR : Afficher le message
                showAlert('danger', data.message || data.erreur || 'Erreur lors de la modération');
                
                // Réactiver les boutons
                if (ligne) {
                    const boutons = ligne.querySelectorAll('.btn');
                    boutons.forEach(btn => btn.disabled = false);
                }
            }
        })
        .catch(error => {
            console.error('Erreur AJAX:', error);
            showAlert('danger', 'Erreur technique lors de la modération');
            
            // Réactiver les boutons en cas d'erreur
            if (ligne) {
                const boutons = ligne.querySelectorAll('.btn');
                boutons.forEach(btn => btn.disabled = false);
            }
        });
    }
    
    /**
     * Met à jour l'interface après modération
     */
    function updateInterface(trajetId, decision) {
        const ligne = document.querySelector(`tr[data-trajet-id="${trajetId}"]`);
        if (!ligne) return;
        
        // Mettre à jour le badge statut
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
        
        // Mettre à jour les boutons
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
     * Met à jour les statistiques en haut
     */
    function updateStats(decision) {
        const enAttenteElement = document.querySelector('.stat-warning .stat-value');
        const validesElement = document.querySelector('.stat-success .stat-value');
        const refusesElement = document.querySelector('.stat-danger .stat-value');
        
        if (decision === 'valide') {
            // Diminuer "En attente", augmenter "Validés"
            if (enAttenteElement) {
                const current = parseInt(enAttenteElement.textContent) || 0;
                enAttenteElement.textContent = Math.max(0, current - 1);
            }
            if (validesElement) {
                const current = parseInt(validesElement.textContent) || 0;
                validesElement.textContent = current + 1;
            }
        } else if (decision === 'refuse') {
            // Diminuer "En attente", augmenter "Refusés"
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
     * Affiche une notification temporaire
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
        
        // Supprimer automatiquement après 5 secondes
        setTimeout(() => {
            const alertElement = document.getElementById(alertId);
            if (alertElement) {
                alertElement.remove();
            }
        }, 5000);
    }
    
    /**
     * Crée le conteneur des alertes s'il n'existe pas
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
