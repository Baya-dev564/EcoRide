/**
 * Script JavaScript spécifique à la vue show.php - Profil conducteur EcoRide
 * Je gère les statistiques, filtres et animations de la page de profil
 */

// Variables globales et configuration
const SHOW_CONFIG = {
    animationDuration: 800,
    progressAnimationDelay: 200,
    filterAnimationDuration: 400,
    statisticsAnimationDuration: 1000,
    tooltipDelay: 300
};

// J'initialise l'état de la vue
let showState = {
    currentFilter: 'all',
    visibleAvis: [],
    statisticsLoaded: false,
    animationsComplete: false
};

// Je cache les éléments DOM
let showElements = {
    avisContainer: null,
    filterDropdown: null,
    filterItems: null,
    progressBars: null,
    statCards: null,
    avisItems: null
};

/**
 * Classe pour gérer l'animation des statistiques
 */
class StatisticsAnimator {
    constructor() {
        this.statCards = document.querySelectorAll('.stat-card');
        this.progressBars = document.querySelectorAll('.progress-bar');
        this.isAnimated = false;
        
        this.initObserver();
    }
    
    /**
     * J'initialise l'observateur d'intersection pour les animations
     */
    initObserver() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !this.isAnimated) {
                    this.animateStatistics();
                    this.isAnimated = true;
                }
            });
        }, {
            threshold: 0.3
        });
        
        // J'observe les cartes de statistiques
        this.statCards.forEach(card => {
            observer.observe(card);
        });
    }
    
    /**
     * J'anime les statistiques avec des effets séquentiels
     */
    animateStatistics() {
        // J'anime les cartes de statistiques
        this.statCards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    card.style.transition = `opacity ${SHOW_CONFIG.animationDuration}ms ease, transform ${SHOW_CONFIG.animationDuration}ms ease`;
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 50);
            }, index * 150);
        });
        
        // J'anime les barres de progression
        setTimeout(() => {
            this.animateProgressBars();
        }, SHOW_CONFIG.progressAnimationDelay);
        
        showState.statisticsLoaded = true;
    }
    
    /**
     * J'anime les barres de progression
     */
    animateProgressBars() {
        this.progressBars.forEach((bar, index) => {
            const targetWidth = bar.style.width || bar.getAttribute('aria-valuenow') + '%';
            
            // Je remet la barre à zéro
            bar.style.width = '0%';
            
            setTimeout(() => {
                bar.style.transition = `width ${SHOW_CONFIG.animationDuration}ms ease`;
                bar.style.width = targetWidth;
            }, index * 100);
        });
    }
    
    /**
     * J'anime les valeurs numériques (compteur)
     * @param {Element} element - Élément contenant la valeur
     * @param {number} targetValue - Valeur cible
     */
    animateValue(element, targetValue) {
        const startValue = 0;
        const duration = SHOW_CONFIG.statisticsAnimationDuration;
        const startTime = performance.now();
        
        function updateValue(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Fonction d'easing
            const easedProgress = 1 - Math.pow(1 - progress, 3);
            
            const currentValue = startValue + (targetValue - startValue) * easedProgress;
            element.textContent = Math.round(currentValue * 10) / 10;
            
            if (progress < 1) {
                requestAnimationFrame(updateValue);
            } else {
                element.textContent = targetValue;
            }
        }
        
        requestAnimationFrame(updateValue);
    }
}

/**
 * Classe pour gérer le filtrage des avis
 */
class AvisFilter {
    constructor() {
        this.filterDropdown = document.getElementById('filterDropdown');
        this.filterItems = document.querySelectorAll('[data-filter]');
        this.avisContainer = document.getElementById('avisContainer');
        this.avisItems = document.querySelectorAll('.avis-item');
        
        this.initEvents();
    }
    
    /**
     * J'initialise les événements des filtres
     */
    initEvents() {
        this.filterItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const filterValue = item.dataset.filter;
                this.applyFilter(filterValue);
                this.updateActiveFilter(item);
            });
        });
    }
    
    /**
     * J'applique un filtre aux avis
     * @param {string} filterValue - Valeur du filtre
     */
    applyFilter(filterValue) {
        showState.currentFilter = filterValue;
        let visibleCount = 0;
        
        // J'affiche l'animation de chargement
        this.showFilterLoading();
        
        setTimeout(() => {
            this.avisItems.forEach((item, index) => {
                const noteItem = parseInt(item.dataset.note);
                let shouldShow = false;
                
                if (filterValue === 'all') {
                    shouldShow = true;
                } else {
                    shouldShow = noteItem === parseInt(filterValue);
                }
                
                if (shouldShow) {
                    this.showAvisItem(item, index);
                    visibleCount++;
                } else {
                    this.hideAvisItem(item);
                }
            });
            
            // Je mets à jour le compteur
            this.updateAvisCount(visibleCount);
            
            // Je masque l'animation de chargement
            this.hideFilterLoading();
            
            // J'affiche le message si aucun résultat
            if (visibleCount === 0) {
                this.showNoResultsMessage(filterValue);
            } else {
                this.hideNoResultsMessage();
            }
        }, SHOW_CONFIG.filterAnimationDuration);
    }
    
    /**
     * J'affiche un avis avec animation
     * @param {Element} item - Élément avis à afficher
     * @param {number} index - Index pour l'animation séquentielle
     */
    showAvisItem(item, index) {
        item.style.display = 'block';
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            item.style.transition = `opacity ${SHOW_CONFIG.filterAnimationDuration}ms ease, transform ${SHOW_CONFIG.filterAnimationDuration}ms ease`;
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, index * 100);
    }
    
    /**
     * Je masque un avis avec animation
     * @param {Element} item - Élément avis à masquer
     */
    hideAvisItem(item) {
        item.style.transition = `opacity ${SHOW_CONFIG.filterAnimationDuration}ms ease, transform ${SHOW_CONFIG.filterAnimationDuration}ms ease`;
        item.style.opacity = '0';
        item.style.transform = 'translateY(-20px)';
        
        setTimeout(() => {
            item.style.display = 'none';
        }, SHOW_CONFIG.filterAnimationDuration);
    }
    
    /**
     * Je mets à jour le filtre actif visuellement
     * @param {Element} activeItem - Élément de filtre actif
     */
    updateActiveFilter(activeItem) {
        this.filterItems.forEach(item => {
            item.classList.remove('active');
        });
        activeItem.classList.add('active');
        
        // Je mets à jour le texte du bouton dropdown
        const filterText = activeItem.textContent;
        this.filterDropdown.innerHTML = `
            <i class="fas fa-filter me-1" aria-hidden="true"></i>
            ${filterText}
        `;
    }
    
    /**
     * J'affiche l'animation de chargement du filtre
     */
    showFilterLoading() {
        const loader = document.createElement('div');
        loader.className = 'text-center py-3';
        loader.id = 'filterLoader';
        loader.innerHTML = `
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Filtrage...</span>
            </div>
            <span class="ms-2 text-muted">Filtrage en cours...</span>
        `;
        
        this.avisContainer.appendChild(loader);
    }
    
    /**
     * Je masque l'animation de chargement du filtre
     */
    hideFilterLoading() {
        const loader = document.getElementById('filterLoader');
        if (loader) {
            loader.remove();
        }
    }
    
    /**
     * Je mets à jour le compteur d'avis
     * @param {number} count - Nombre d'avis visibles
     */
    updateAvisCount(count) {
        const title = document.querySelector('.card-header h3');
        if (title) {
            title.innerHTML = `
                <i class="fas fa-comments text-primary me-2" aria-hidden="true"></i>
                ${showState.currentFilter === 'all' ? 'Tous les avis' : 'Avis filtrés'} (${count})
            `;
        }
    }
    
    /**
     * J'affiche le message "Aucun résultat"
     * @param {string} filterValue - Valeur du filtre
     */
    showNoResultsMessage(filterValue) {
        this.hideNoResultsMessage();
        
        const message = document.createElement('div');
        message.className = 'alert alert-warning text-center py-4';
        message.id = 'noResultsMessage';
        message.innerHTML = `
            <i class="fas fa-search fa-2x mb-3 text-muted"></i>
            <h4 class="h5">Aucun avis avec ${filterValue} étoile${filterValue > 1 ? 's' : ''}</h4>
            <p class="text-muted">Ce conducteur n'a pas encore reçu d'avis avec cette note.</p>
            <button class="btn btn-outline-primary btn-sm" onclick="showAvisFilter.applyFilter('all')">
                <i class="fas fa-times me-1"></i>
                Voir tous les avis
            </button>
        `;
        
        this.avisContainer.appendChild(message);
    }
    
    /**
     * Je masque le message "Aucun résultat"
     */
    hideNoResultsMessage() {
        const message = document.getElementById('noResultsMessage');
        if (message) {
            message.remove();
        }
    }
}

/**
 * Classe pour gérer les tooltips informatifs
 */
class TooltipManager {
    constructor() {
        this.tooltips = new Map();
        this.initTooltips();
    }
    
    /**
     * J'initialise les tooltips
     */
    initTooltips() {
        // Tooltips pour les statistiques
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(card => {
            this.addTooltip(card, this.getStatTooltip(card));
        });
        
        // Tooltips pour les barres de progression
        const progressBars = document.querySelectorAll('.progress-bar');
        progressBars.forEach(bar => {
            this.addTooltip(bar, this.getProgressTooltip(bar));
        });
    }
    
    /**
     * J'ajoute un tooltip à un élément
     * @param {Element} element - Élément cible
     * @param {string} text - Texte du tooltip
     */
    addTooltip(element, text) {
        let tooltip = null;
        
        element.addEventListener('mouseenter', (e) => {
            setTimeout(() => {
                tooltip = this.createTooltip(text);
                this.positionTooltip(tooltip, e.target);
                document.body.appendChild(tooltip);
                
                setTimeout(() => {
                    tooltip.classList.add('show');
                }, 10);
            }, SHOW_CONFIG.tooltipDelay);
        });
        
        element.addEventListener('mouseleave', () => {
            if (tooltip) {
                tooltip.classList.remove('show');
                setTimeout(() => {
                    if (tooltip.parentNode) {
                        tooltip.parentNode.removeChild(tooltip);
                    }
                }, 300);
            }
        });
    }
    
    /**
     * Je crée un élément tooltip
     * @param {string} text - Texte du tooltip
     * @returns {Element} Élément tooltip
     */
    createTooltip(text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip-custom';
        tooltip.textContent = text;
        return tooltip;
    }
    
    /**
     * Je positionne le tooltip par rapport à l'élément cible
     * @param {Element} tooltip - Élément tooltip
     * @param {Element} target - Élément cible
     */
    positionTooltip(tooltip, target) {
        const rect = target.getBoundingClientRect();
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
        
        tooltip.style.left = `${rect.left + scrollLeft + rect.width / 2}px`;
        tooltip.style.top = `${rect.top + scrollTop - 10}px`;
        tooltip.style.transform = 'translateX(-50%) translateY(-100%)';
    }
    
    /**
     * Je génère le texte de tooltip pour une statistique
     * @param {Element} card - Carte de statistique
     * @returns {string} Texte du tooltip
     */
    getStatTooltip(card) {
        const value = card.querySelector('.stat-value').textContent;
        const label = card.querySelector('.stat-label').textContent;
        
        return `${label}: ${value}`;
    }
    
    /**
     * Je génère le texte de tooltip pour une barre de progression
     * @param {Element} bar - Barre de progression
     * @returns {string} Texte du tooltip
     */
    getProgressTooltip(bar) {
        const value = bar.getAttribute('aria-valuenow');
        const max = bar.getAttribute('aria-valuemax');
        
        return `${value}/${max}`;
    }
}

/**
 * Classe pour gérer les animations avancées
 */
class AdvancedAnimations {
    constructor() {
        this.initScrollAnimations();
        this.initHoverEffects();
        this.initParallaxEffects();
    }
    
    /**
     * J'initialise les animations au scroll
     */
    initScrollAnimations() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    this.animateElement(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });
        
        // J'observe les éléments à animer
        document.querySelectorAll('.avis-item, .card, .stat-card').forEach(element => {
            observer.observe(element);
        });
    }
    
    /**
     * J'initialise les effets hover avancés
     */
    initHoverEffects() {
        const cards = document.querySelectorAll('.card, .stat-card');
        
        cards.forEach(card => {
            card.addEventListener('mouseenter', (e) => {
                this.createRippleEffect(e);
            });
        });
    }
    
    /**
     * J'initialise les effets parallax subtils
     */
    initParallaxEffects() {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            
            document.querySelectorAll('.stat-card').forEach((card, index) => {
                card.style.transform = `translateY(${rate * (index + 1) * 0.1}px)`;
            });
        });
    }
    
    /**
     * J'anime un élément spécifique
     * @param {Element} element - Élément à animer
     */
    animateElement(element) {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        
        setTimeout(() => {
            element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, 100);
    }
    
    /**
     * Je crée un effet de ripple au clic
     * @param {Event} e - Événement de clic
     */
    createRippleEffect(e) {
        const ripple = document.createElement('span');
        const rect = e.target.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            left: ${x}px;
            top: ${y}px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s ease-out;
            pointer-events: none;
        `;
        
        e.target.style.position = 'relative';
        e.target.style.overflow = 'hidden';
        e.target.appendChild(ripple);
        
        setTimeout(() => {
            ripple.remove();
        }, 600);
    }
}

/**
 * Classe pour optimiser les performances
 */
class PerformanceOptimizer {
    constructor() {
        this.throttledFunctions = new Map();
        this.optimizeEventListeners();
    }
    
    /**
     * J'optimise les event listeners
     */
    optimizeEventListeners() {
        // Je crée un debounce pour les événements de scroll
        const throttledScroll = this.throttle(() => {
            this.handleScroll();
        }, 16); // 60 FPS
        
        window.addEventListener('scroll', throttledScroll, { passive: true });
        
        // J'optimise les événements de resize
        const throttledResize = this.throttle(() => {
            this.handleResize();
        }, 250);
        
        window.addEventListener('resize', throttledResize);
    }
    
    /**
     * Je throttle une fonction
     * @param {Function} func - Fonction à throttler
     * @param {number} delay - Délai en millisecondes
     * @returns {Function} Fonction throttlée
     */
    throttle(func, delay) {
        let timeoutId;
        let lastExecTime = 0;
        
        return function(...args) {
            const currentTime = Date.now();
            
            if (currentTime - lastExecTime > delay) {
                func.apply(this, args);
                lastExecTime = currentTime;
            } else {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {
                    func.apply(this, args);
                    lastExecTime = Date.now();
                }, delay - (currentTime - lastExecTime));
            }
        };
    }
    
    /**
     * Je gère les événements de scroll optimisés
     */
    handleScroll() {
        // Je mets à jour les éléments visibles
        this.updateVisibleElements();
    }
    
    /**
     * Je gère les événements de resize optimisés
     */
    handleResize() {
        // Je recalcule les positions des tooltips
        this.recalculateTooltips();
    }
    
    /**
     * Je mets à jour les éléments visibles
     */
    updateVisibleElements() {
        const viewportHeight = window.innerHeight;
        const scrollTop = window.pageYOffset;
        
        document.querySelectorAll('.avis-item').forEach(item => {
            const rect = item.getBoundingClientRect();
            const isVisible = rect.top < viewportHeight && rect.bottom > 0;
            
            if (isVisible && !item.classList.contains('viewport-visible')) {
                item.classList.add('viewport-visible');
                this.optimizeElementRendering(item);
            }
        });
    }
    
    /**
     * J'optimise le rendu d'un élément
     * @param {Element} element - Élément à optimiser
     */
    optimizeElementRendering(element) {
        // Lazy loading des images si nécessaire
        const images = element.querySelectorAll('img[data-src]');
        images.forEach(img => {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
        });
    }
    
    /**
     * Je recalcule les positions des tooltips
     */
    recalculateTooltips() {
        // Logique de recalcul des tooltips après resize
    }
}

/**
 * Fonction d'initialisation de la vue show
 */
function initShowAvis() {
    // Je vérifie que le DOM est chargé
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initShowAvis);
        return;
    }
    
    // Je vérifie que nous sommes sur la bonne page
    if (!document.querySelector('.avis-item')) {
        return;
    }
    
    // J'initialise les différents modules
    const statisticsAnimator = new StatisticsAnimator();
    const avisFilter = new AvisFilter();
    const tooltipManager = new TooltipManager();
    const advancedAnimations = new AdvancedAnimations();
    const performanceOptimizer = new PerformanceOptimizer();
    
    // J'expose les instances pour le débogage
    window.statisticsAnimator = statisticsAnimator;
    window.showAvisFilter = avisFilter;
    window.tooltipManager = tooltipManager;
    window.advancedAnimations = advancedAnimations;
    window.performanceOptimizer = performanceOptimizer;
    
    // J'ajoute les styles CSS pour les animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        .viewport-visible {
            transition: all 0.3s ease;
        }
        
        .animate-in {
            animation: slideInUp 0.6s ease-out;
        }
    `;
    document.head.appendChild(style);
    
    showState.animationsComplete = true;
}

// J'initialise automatiquement
initShowAvis();
