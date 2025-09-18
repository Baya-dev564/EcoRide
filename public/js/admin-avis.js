/**
 * JavaScript pour la gestion des avis admin EcoRide
 * Je gère toutes les interactions AJAX de modération des avis
 * 
 * Fonctionnalités :
 * - Modification du statut des avis
 * - Suppression des avis avec confirmation
 * - Feedback visuel et notifications
 * - Gestion des erreurs
 */

document.addEventListener('DOMContentLoaded', function() {
    // J'initialise la gestion des avis admin
    console.log('🎯 Admin Avis JS - Initialisation');
    
    // Je récupère les éléments principaux
    const changeStatusButtons = document.querySelectorAll('.change-status-btn');
    const deleteButtons = document.querySelectorAll('.delete-avis-btn');
    const confirmDeleteModal = document.getElementById('confirmDeleteModal');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    
    // Variables pour la suppression
    let avisIdToDelete = null;
    let deleteButtonElement = null;
    
    /**
     * Je gère les changements de statut des avis
     */
    changeStatusButtons.forEach(button => {
        button.addEventListener('click', function() {
            const avisId = this.dataset.avisId;
            const newStatus = this.dataset.status;
            const avisCard = this.closest('.avis-card');
            
            // Je change le statut via AJAX
            changeAvisStatus(avisId, newStatus, avisCard, this);
        });
    });
    
    /**
     * Je gère la suppression des avis
     */
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            avisIdToDelete = this.dataset.avisId;
            deleteButtonElement = this;
            
            // J'ouvre la modal de confirmation
            if (confirmDeleteModal) {
                const modal = new bootstrap.Modal(confirmDeleteModal);
                modal.show();
            }
        });
    });
    
    /**
     * Je confirme la suppression
     */
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            if (avisIdToDelete && deleteButtonElement) {
                const avisCard = deleteButtonElement.closest('.avis-card');
                
                // Je supprime l'avis
                deleteAvis(avisIdToDelete, avisCard);
                
                // Je ferme la modal
                const modal = bootstrap.Modal.getInstance(confirmDeleteModal);
                modal.hide();
                
                // Je reset les variables
                avisIdToDelete = null;
                deleteButtonElement = null;
            }
        });
    }
    
    /**
     * Je change le statut d'un avis via AJAX
     * 
     * @param {string} avisId - ID de l'avis MongoDB
     * @param {string} newStatus - Nouveau statut (actif, masque, signale)
     * @param {HTMLElement} avisCard - Élément de la carte avis
     * @param {HTMLElement} button - Bouton cliqué
     */
    function changeAvisStatus(avisId, newStatus, avisCard, button) {
        // Je désactive le bouton pendant la requête
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        // Je prépare les données
        const formData = {
            avis_id: avisId,
            statut: newStatus
        };
        
        // Je fais la requête AJAX
        fetch('/admin/api/avis-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Je mets à jour l'interface
                updateAvisStatusUI(avisCard, newStatus);
                
                // Je montre un message de succès
                showNotification('Statut mis à jour avec succès !', 'success');
                
                console.log('✅ Statut avis mis à jour:', avisId, '→', newStatus);
            } else {
                throw new Error(data.message || 'Erreur lors de la modification du statut');
            }
        })
        .catch(error => {
            console.error('❌ Erreur changement statut avis:', error);
            showNotification('Erreur lors de la modification du statut: ' + error.message, 'error');
        })
        .finally(() => {
            // Je réactive le bouton
            button.disabled = false;
            button.innerHTML = originalText;
        });
    }
    
    /**
     * Je supprime un avis via AJAX
     * 
     * @param {string} avisId - ID de l'avis à supprimer
     * @param {HTMLElement} avisCard - Élément de la carte avis
     */
    function deleteAvis(avisId, avisCard) {
        // J'ajoute un indicateur de chargement
        avisCard.style.opacity = '0.5';
        avisCard.style.pointerEvents = 'none';
        
        // Je fais la requête de suppression
        fetch(`/admin/api/avis-delete/${avisId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // J'anime la suppression
                avisCard.style.transform = 'scale(0.95)';
                avisCard.style.opacity = '0';
                
                // Je retire l'élément après l'animation
                setTimeout(() => {
                    avisCard.remove();
                    
                    // Je vérifie s'il reste des avis
                    checkEmptyState();
                }, 300);
                
                // Je montre un message de succès
                showNotification('Avis supprimé définitivement', 'success');
                
                console.log('✅ Avis supprimé:', avisId);
            } else {
                throw new Error(data.message || 'Erreur lors de la suppression');
            }
        })
        .catch(error => {
            console.error('❌ Erreur suppression avis:', error);
            
            // Je restaure l'état de la carte
            avisCard.style.opacity = '1';
            avisCard.style.pointerEvents = 'auto';
            
            showNotification('Erreur lors de la suppression: ' + error.message, 'error');
        });
    }
    
    /**
     * Je mets à jour l'interface après changement de statut
     * 
     * @param {HTMLElement} avisCard - Carte de l'avis
     * @param {string} newStatus - Nouveau statut
     */
    function updateAvisStatusUI(avisCard, newStatus) {
        const statusBadge = avisCard.querySelector('.status-badge');
        
        if (statusBadge) {
            // Je supprime les anciennes classes
            statusBadge.className = 'status-badge';
            
            // J'ajoute la nouvelle classe
            statusBadge.classList.add(`status-badge--${newStatus}`);
            
            // Je mets à jour le contenu
            const statusIcons = {
                'actif': '🟢',
                'masque': '🟡', 
                'signale': '🟠',
                'supprime': '🔴'
            };
            
            const icon = statusIcons[newStatus] || '🟢';
            const text = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
            
            statusBadge.innerHTML = `${icon} ${text}`;
            
            // J'anime le changement
            statusBadge.style.transform = 'scale(1.1)';
            setTimeout(() => {
                statusBadge.style.transform = 'scale(1)';
            }, 200);
        }
    }
    
    /**
     * Je vérifie s'il faut afficher l'état vide
     */
    function checkEmptyState() {
        const avisGrid = document.querySelector('.avis-grid');
        const avisCards = document.querySelectorAll('.avis-card');
        
        if (avisCards.length === 0 && avisGrid) {
            // Je crée l'état vide
            const emptyState = document.createElement('div');
            emptyState.className = 'empty-state';
            emptyState.innerHTML = `
                <div class="empty-state-icon">
                    <i class="fas fa-star" aria-hidden="true"></i>
                </div>
                <h3 class="empty-state-title">Aucun avis restant</h3>
                <p class="empty-state-description">
                    Tous les avis ont été traités ou supprimés.
                </p>
            `;
            
            // Je remplace la grille par l'état vide
            avisGrid.parentNode.replaceChild(emptyState, avisGrid);
        }
    }
    
    /**
     * Je montre une notification à l'utilisateur
     * 
     * @param {string} message - Message à afficher
     * @param {string} type - Type de notification (success, error, info)
     */
    function showNotification(message, type = 'info') {
        // Je crée la notification
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show notification-toast`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        
        const icon = type === 'success' ? 'check-circle' : 
                    type === 'error' ? 'exclamation-circle' : 'info-circle';
        
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${icon} me-2"></i>
                <span>${message}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // J'ajoute au DOM
        document.body.appendChild(notification);
        
        // Je supprime automatiquement après 5 secondes
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
    
    /**
     * Je gère les filtres en temps réel
     */
    const filtersForm = document.getElementById('avisFiltersForm');
    if (filtersForm) {
        const filterSelects = filtersForm.querySelectorAll('select');
        
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                // Je soumets automatiquement le formulaire
                filtersForm.submit();
            });
        });
    }
    
    /**
     * J'ajoute des animations au survol
     */
    const avisCards = document.querySelectorAll('.avis-card');
    avisCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 20px rgba(0,0,0,0.1)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });
    
    console.log('✅ Admin Avis JS - Initialisé avec succès');
});

/**
 * Fonction utilitaire pour formater les dates
 * 
 * @param {string} dateString - Date au format ISO
 * @return {string} Date formatée
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Fonction pour recharger la page avec de nouveaux filtres
 * 
 * @param {Object} filters - Objet avec les filtres
 */
function applyFilters(filters) {
    const url = new URL(window.location);
    
    // Je supprime les anciens paramètres
    url.searchParams.delete('note');
    url.searchParams.delete('statut');
    
    // J'ajoute les nouveaux filtres
    Object.keys(filters).forEach(key => {
        if (filters[key]) {
            url.searchParams.set(key, filters[key]);
        }
    });
    
    // Je navigue vers la nouvelle URL
    window.location.href = url.toString();
}
