/**
 * Script JavaScript personnalisé pour le système d'avis EcoRide
 * Utilise JavaScript natif (pas de jQuery) selon l'énoncé du TP
 * Gère l'interactivité et les fonctionnalités dynamiques des avis
 */

// ===========================================
// VARIABLES GLOBALES ET CONFIGURATION
// ===========================================

// Configuration générale du système d'avis
const AVIS_CONFIG = {
    maxStars: 5,
    minCommentLength: 10,
    maxCommentLength: 500,
    animationDuration: 300,
    filterDelay: 500
};

// Cache pour les éléments DOM fréquemment utilisés
let cachedElements = {
    avisContainer: null,
    filterForm: null,
    sortButtons: null,
    avisCards: null
};

// ===========================================
// FONCTIONS UTILITAIRES
// ===========================================

/**
 * Fonction pour récupérer un élément DOM avec cache
 * @param {string} selector - Sélecteur CSS
 * @param {string} cacheKey - Clé de cache (optionel)
 * @returns {Element} Élément DOM
 */
function getElement(selector, cacheKey = null) {
    if (cacheKey && cachedElements[cacheKey]) {
        return cachedElements[cacheKey];
    }
    
    const element = document.querySelector(selector);
    if (cacheKey) {
        cachedElements[cacheKey] = element;
    }
    
    return element;
}

/**
 * Fonction pour récupérer plusieurs éléments DOM
 * @param {string} selector - Sélecteur CSS
 * @returns {NodeList} Liste d'éléments DOM
 */
function getElements(selector) {
    return document.querySelectorAll(selector);
}

/**
 * Fonction pour débouncer les événements (éviter les appels trop fréquents)
 * @param {Function} func - Fonction à exécuter
 * @param {number} delay - Délai en millisecondes
 * @returns {Function} Fonction debouncée
 */
function debounce(func, delay) {
    let timeoutId;
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
}

/**
 * Fonction pour afficher une animation de chargement
 * @param {Element} container - Conteneur où afficher le loader
 */
function showLoader(container) {
    const loader = document.createElement('div');
    loader.className = 'text-center py-5';
    loader.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Chargement...</span>
        </div>
        <p class="mt-2 text-muted">Chargement des avis...</p>
    `;
    loader.id = 'avisLoader';
    container.appendChild(loader);
}

/**
 * Fonction pour masquer l'animation de chargement
 */
function hideLoader() {
    const loader = document.getElementById('avisLoader');
    if (loader) {
        loader.remove();
    }
}

// ===========================================
// GESTION DES FILTRES
// ===========================================

/**
 * Classe pour gérer les filtres des avis
 */
class AvisFilter {
    constructor() {
        this.filterForm = getElement('#filterForm', 'filterForm');
        this.noteFilter = getElement('#noteFilter');
        this.dateFilter = getElement('#dateFilter');
        this.avisContainer = getElement('#avisContainer', 'avisContainer');
        
        this.initEvents();
    }
    
    /**
     * Initialise les événements des filtres
     */
    initEvents() {
        if (this.filterForm) {
            // Événement de soumission du formulaire
            this.filterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.applyFilters();
            });
            
            // Événement de changement des filtres (temps réel)
            this.noteFilter.addEventListener('change', 
                debounce(() => this.applyFilters(), AVIS_CONFIG.filterDelay)
            );
            
            this.dateFilter.addEventListener('change', 
                debounce(() => this.applyFilters(), AVIS_CONFIG.filterDelay)
            );
        }
    }
    
    /**
     * Applique les filtres sélectionnés
     */
    applyFilters() {
        const noteMin = parseInt(this.noteFilter.value) || 0;
        const datePeriod = this.dateFilter.value;
        
        // Récupération de toutes les cartes d'avis
        const avisCards = getElements('.avis-card');
        let visibleCount = 0;
        
        // Affichage du loader pendant le filtrage
        showLoader(this.avisContainer);
        
        // Simulation d'un délai pour l'effet de chargement
        setTimeout(() => {
            avisCards.forEach(card => {
                const noteCard = parseInt(card.dataset.note) || 0;
                const dateCard = new Date(card.querySelector('.text-muted').textContent.split(' ')[1]);
                
                // Vérification du filtre par note
                const noteMatch = noteCard >= noteMin;
                
                // Vérification du filtre par date
                const dateMatch = this.checkDateFilter(dateCard, datePeriod);
                
                if (noteMatch && dateMatch) {
                    this.showCard(card);
                    visibleCount++;
                } else {
                    this.hideCard(card);
                }
            });
            
            // Mise à jour du compteur
            this.updateCounter(visibleCount);
            
            // Masquage du loader
            hideLoader();
            
            // Affichage d'un message si aucun résultat
            if (visibleCount === 0) {
                this.showNoResultsMessage();
            } else {
                this.hideNoResultsMessage();
            }
        }, 300);
    }
    
    /**
     * Vérifie si une date correspond au filtre sélectionné
     * @param {Date} date - Date à vérifier
     * @param {string} period - Période sélectionnée
     * @returns {boolean} True si la date correspond
     */
    checkDateFilter(date, period) {
        if (!period) return true;
        
        const now = new Date();
        const diffTime = now.getTime() - date.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        switch (period) {
            case 'week':
                return diffDays <= 7;
            case 'month':
                return diffDays <= 30;
            case 'year':
                return diffDays <= 365;
            default:
                return true;
        }
    }
    
    /**
     * Affiche une carte d'avis avec animation
     * @param {Element} card - Carte à afficher
     */
    showCard(card) {
        card.style.display = 'block';
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = `opacity ${AVIS_CONFIG.animationDuration}ms ease, transform ${AVIS_CONFIG.animationDuration}ms ease`;
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 50);
    }
    
    /**
     * Masque une carte d'avis avec animation
     * @param {Element} card - Carte à masquer
     */
    hideCard(card) {
        card.style.transition = `opacity ${AVIS_CONFIG.animationDuration}ms ease, transform ${AVIS_CONFIG.animationDuration}ms ease`;
        card.style.opacity = '0';
        card.style.transform = 'translateY(-20px)';
        
        setTimeout(() => {
            card.style.display = 'none';
        }, AVIS_CONFIG.animationDuration);
    }
    
    /**
     * Met à jour le compteur d'avis
     * @param {number} count - Nombre d'avis visibles
     */
    updateCounter(count) {
        const counter = getElement('#avisCount');
        if (counter) {
            counter.textContent = `${count} avis`;
            counter.style.animation = 'pulse 0.5s ease';
        }
    }
    
    /**
     * Affiche le message "Aucun résultat"
     */
    showNoResultsMessage() {
        this.hideNoResultsMessage(); // Supprimer le message précédent s'il existe
        
        const message = document.createElement('div');
        message.className = 'alert alert-warning text-center py-4';
        message.id = 'noResultsMessage';
        message.innerHTML = `
            <i class="fas fa-search fa-2x mb-3 text-muted"></i>
            <h4 class="h5">Aucun avis trouvé</h4>
            <p class="text-muted">Essayez de modifier vos critères de recherche.</p>
            <button class="btn btn-outline-primary btn-sm" onclick="avisFilter.clearFilters()">
                <i class="fas fa-times me-1"></i>
                Effacer les filtres
            </button>
        `;
        
        this.avisContainer.appendChild(message);
    }
    
    /**
     * Masque le message "Aucun résultat"
     */
    hideNoResultsMessage() {
        const message = getElement('#noResultsMessage');
        if (message) {
            message.remove();
        }
    }
    
    /**
     * Efface tous les filtres
     */
    clearFilters() {
        this.noteFilter.value = '';
        this.dateFilter.value = '';
        this.applyFilters();
    }
}

// ===========================================
// GESTION DU TRI
// ===========================================

/**
 * Classe pour gérer le tri des avis
 */
class AvisSort {
    constructor() {
        this.sortButtons = getElements('[data-sort]');
        this.avisContainer = getElement('#avisContainer', 'avisContainer');
        
        this.initEvents();
    }
    
    /**
     * Initialise les événements de tri
     */
    initEvents() {
        this.sortButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const sortType = button.dataset.sort;
                this.sortAvis(sortType);
                this.updateSortButton(button);
            });
        });
    }
    
    /**
     * Trie les avis selon le type spécifié
     * @param {string} sortType - Type de tri (date, note, note-desc)
     */
    sortAvis(sortType) {
        const avisCards = Array.from(getElements('.avis-card'));
        
        // Tri des cartes selon le type
        avisCards.sort((a, b) => {
            switch (sortType) {
                case 'date':
                    return new Date(b.querySelector('.text-muted').textContent.split(' ')[1]) - 
                           new Date(a.querySelector('.text-muted').textContent.split(' ')[1]);
                case 'note':
                    return parseInt(a.dataset.note) - parseInt(b.dataset.note);
                case 'note-desc':
                    return parseInt(b.dataset.note) - parseInt(a.dataset.note);
                default:
                    return 0;
            }
        });
        
        // Réorganisation des cartes dans le DOM
        avisCards.forEach((card, index) => {
            card.style.order = index;
            card.style.animation = `fadeIn 0.5s ease ${index * 0.1}s both`;
        });
    }
    
    /**
     * Met à jour l'apparence du bouton de tri actif
     * @param {Element} activeButton - Bouton actuellement actif
     */
    updateSortButton(activeButton) {
        this.sortButtons.forEach(button => {
            button.classList.remove('active');
        });
        activeButton.classList.add('active');
    }
}

// ===========================================
// GESTION DES ÉTOILES (POUR LE FORMULAIRE)
// ===========================================

/**
 * Classe pour gérer l'affichage interactif des étoiles
 */
class StarRating {
    constructor(container) {
        this.container = container;
        this.stars = container.querySelectorAll('.star');
        this.hiddenInput = container.querySelector('input[type="hidden"]');
        this.rating = 0;
        
        this.initEvents();
    }
    
    /**
     * Initialise les événements des étoiles
     */
    initEvents() {
        this.stars.forEach((star, index) => {
            star.addEventListener('click', () => {
                this.setRating(index + 1);
            });
            
            star.addEventListener('mouseenter', () => {
                this.highlightStars(index + 1);
            });
            
            star.addEventListener('mouseleave', () => {
                this.highlightStars(this.rating);
            });
        });
    }
    
    /**
     * Définit la note sélectionnée
     * @param {number} rating - Note sélectionnée
     */
    setRating(rating) {
        this.rating = rating;
        this.hiddenInput.value = rating;
        this.highlightStars(rating);
    }
    
    /**
     * Met en surbrillance les étoiles
     * @param {number} count - Nombre d'étoiles à mettre en surbrillance
     */
    highlightStars(count) {
        this.stars.forEach((star, index) => {
            if (index < count) {
                star.classList.add('active');
                star.classList.remove('inactive');
            } else {
                star.classList.remove('active');
                star.classList.add('inactive');
            }
        });
    }
}

// ===========================================
// GESTION DES ANIMATIONS
// ===========================================

/**
 * Classe pour gérer les animations des avis
 */
class AvisAnimations {
    constructor() {
        this.initScrollAnimations();
        this.initHoverAnimations();
    }
    
    /**
     * Initialise les animations au défilement
     */
    initScrollAnimations() {
        // Intersection Observer pour les animations d'apparition
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        });
        
        // Observation des cartes d'avis
        getElements('.avis-card').forEach(card => {
            observer.observe(card);
        });
    }
    
    /**
     * Initialise les animations au survol
     */
    initHoverAnimations() {
        getElements('.avis-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-5px)';
                card.style.boxShadow = '0 1rem 3rem rgba(0, 0, 0, 0.175)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
                card.style.boxShadow = '0 0.125rem 0.25rem rgba(0, 0, 0, 0.075)';
            });
        });
    }
}

// ===========================================
// GESTION DES NOTIFICATIONS
// ===========================================

/**
 * Classe pour gérer les notifications utilisateur
 */
class AvisNotifications {
    /**
     * Affiche une notification de succès
     * @param {string} message - Message à afficher
     */
    static showSuccess(message) {
        this.showNotification(message, 'success');
    }
    
    /**
     * Affiche une notification d'erreur
     * @param {string} message - Message à afficher
     */
    static showError(message) {
        this.showNotification(message, 'error');
    }
    
    /**
     * Affiche une notification
     * @param {string} message - Message à afficher
     * @param {string} type - Type de notification (success, error, info)
     */
    static showNotification(message, type = 'info') {
        // Suppression des notifications précédentes
        const existingNotifications = getElements('.avis-notification');
        existingNotifications.forEach(notif => notif.remove());
        
        // Création de la notification
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show avis-notification`;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '9999';
        notification.style.minWidth = '300px';
        
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Ajout au DOM
        document.body.appendChild(notification);
        
        // Suppression automatique après 5 secondes
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
}

// ===========================================
// INITIALISATION DE L'APPLICATION
// ===========================================

/**
 * Fonction d'initialisation principale
 */
function initAvisSystem() {
    // Vérification que le DOM est chargé
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAvisSystem);
        return;
    }
    
    // Initialisation des différentes fonctionnalités
    const avisFilter = new AvisFilter();
    const avisSort = new AvisSort();
    const avisAnimations = new AvisAnimations();
    
    // Initialisation des étoiles pour les formulaires
    getElements('.star-rating').forEach(container => {
        new StarRating(container);
    });
    
    // Gestion des messages d'URL (success, error)
    handleUrlMessages();
    
    // Exposition des instances globales pour le débogage
    window.avisFilter = avisFilter;
    window.avisSort = avisSort;
    window.avisAnimations = avisAnimations;
}

/**
 * Gestion des messages passés en paramètre URL
 */
function handleUrlMessages() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.get('success') === '1') {
        AvisNotifications.showSuccess('Votre avis a été enregistré avec succès !');
    }
    
    if (urlParams.get('error') === '1') {
        AvisNotifications.showError('Une erreur est survenue lors de l\'enregistrement de votre avis.');
    }
}

// ===========================================
// LANCEMENT DE L'APPLICATION
// ===========================================

// Initialisation automatique du système
initAvisSystem();
