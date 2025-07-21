/**
 * JavaScript pour l'interface d'administration EcoRide
 * Gestion des interactions et fonctionnalités admin
 * Compatible avec Bootstrap 5 et respecte les bonnes pratiques
 */

document.addEventListener('DOMContentLoaded', function() {
    // ========== INITIALISATION ==========
    
    // Initialisation des composants admin
    initStatCards();
    initActionCards();
    initResponsiveFeatures();
    
    // ========== CARTES DE STATISTIQUES ==========
    
    /**
     * Initialise les animations et interactions des cartes de statistiques
     */
    function initStatCards() {
        const statCards = document.querySelectorAll('.stat-card');
        
        statCards.forEach(card => {
            // Animation au survol
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
            
            // Animation des chiffres (compteur)
            const numberElement = card.querySelector('.stat-number');
            if (numberElement) {
                animateNumber(numberElement);
            }
        });
    }
    
    /**
     * Anime un nombre avec un effet de compteur
     * @param {Element} element - Élément contenant le nombre
     */
    function animateNumber(element) {
        const finalNumber = parseInt(element.textContent);
        const duration = 2000; // 2 secondes
        const increment = finalNumber / (duration / 16); // 60 FPS
        let currentNumber = 0;
        
        const timer = setInterval(() => {
            currentNumber += increment;
            if (currentNumber >= finalNumber) {
                element.textContent = finalNumber;
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(currentNumber);
            }
        }, 16);
    }
    
    // ========== CARTES D'ACTION ==========
    
    /**
     * Initialise les interactions des cartes d'action
     */
    function initActionCards() {
        const actionLinks = document.querySelectorAll('.action-link');
        
        actionLinks.forEach(link => {
            // Effet de survol amélioré
            link.addEventListener('mouseenter', function() {
                const icon = this.querySelector('.action-icon');
                if (icon) {
                    icon.style.transform = 'scale(1.1) rotate(5deg)';
                }
            });
            
            link.addEventListener('mouseleave', function() {
                const icon = this.querySelector('.action-icon');
                if (icon) {
                    icon.style.transform = 'scale(1) rotate(0deg)';
                }
            });
            
            // Effet de clic
            link.addEventListener('click', function(e) {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });
    }
    
    // ========== FONCTIONNALITÉS RESPONSIVE ==========
    
    /**
     * Initialise les fonctionnalités responsive
     */
    function initResponsiveFeatures() {
        // Adaptation mobile de la navigation
        const navbarToggler = document.querySelector('.navbar-toggler');
        if (navbarToggler) {
            navbarToggler.addEventListener('click', function() {
                const icon = this.querySelector('.navbar-toggler-icon');
                if (icon) {
                    icon.style.transform = 'rotate(90deg)';
                    setTimeout(() => {
                        icon.style.transform = '';
                    }, 300);
                }
            });
        }
        
        // Adaptation des cartes sur mobile
        handleMobileLayout();
        window.addEventListener('resize', handleMobileLayout);
    }
    
    /**
     * Gère l'affichage mobile des cartes
     */
    function handleMobileLayout() {
        const isMobile = window.innerWidth <= 768;
        const statCards = document.querySelectorAll('.stat-card');
        
        statCards.forEach(card => {
            const cardBody = card.querySelector('.stat-card-body');
            if (cardBody) {
                if (isMobile) {
                    cardBody.style.flexDirection = 'column';
                    cardBody.style.textAlign = 'center';
                } else {
                    cardBody.style.flexDirection = 'row';
                    cardBody.style.textAlign = 'left';
                }
            }
        });
    }
    
    // ========== UTILITAIRES ==========
    
    /**
     * Formate les nombres avec des séparateurs de milliers
     * @param {number} num - Nombre à formater
     * @returns {string} - Nombre formaté
     */
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
    }
    
    /**
     * Affiche une notification toast
     * @param {string} message - Message à afficher
     * @param {string} type - Type de notification (success, error, info)
     */
    function showNotification(message, type = 'info') {
        // Utilisation des toasts Bootstrap si disponible
        const toastContainer = document.querySelector('.toast-container');
        if (toastContainer) {
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type} border-0`;
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${getIconForType(type)} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            toastContainer.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }
    }
    
    /**
     * Retourne l'icône appropriée selon le type de notification
     * @param {string} type - Type de notification
     * @returns {string} - Classe d'icône Font Awesome
     */
    function getIconForType(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
    
    // ========== GESTION DES ERREURS ==========
    
    /**
     * Gère les erreurs globales
     */
    window.addEventListener('error', function(e) {
        showNotification('Une erreur inattendue s\'est produite.', 'error');
    });
});
