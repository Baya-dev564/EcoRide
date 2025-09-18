/**
 * JavaScript pour les graphiques Chart.js du dashboard admin EcoRide
 * Fichier unique contenant toute la logique des graphiques
 * Compatible avec Chart.js 4.4.0 et respectant les bonnes pratiques
 */

(function() {
    'use strict';

    /* ========================================
       1. CONFIGURATION GLOBALE ET COULEURS
    ======================================== */

    // Configuration des couleurs et thèmes (récupère les variables CSS)
    const ADMIN_COLORS = {
        primary: '#0d6efd',
        success: '#198754', 
        info: '#0dcaf0',
        warning: '#ffc107',
        danger: '#dc3545',
        secondary: '#6c757d',
        light: '#f8f9fa',
        dark: '#212529'
    };

    // Dégradés pour les graphiques
    const ADMIN_GRADIENTS = {
        primary: ['#0d6efd', '#0056b3'],
        success: ['#198754', '#146c43'],
        info: ['#0dcaf0', '#0aa2c0'],
        warning: ['#ffc107', '#d39e00'],
        danger: ['#dc3545', '#b02a37']
    };

    // Configuration par défaut de Chart.js pour l'admin
    const DEFAULT_CHART_CONFIG = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        plugins: {
            legend: {
                display: false // On utilise nos légendes personnalisées
            },
            tooltip: {
                backgroundColor: 'rgba(33, 37, 41, 0.9)',
                titleColor: '#ffffff',
                bodyColor: '#ffffff',
                borderColor: ADMIN_COLORS.primary,
                borderWidth: 1,
                cornerRadius: 8,
                displayColors: true,
                padding: 12
            }
        },
        animation: {
            duration: 800,
            easing: 'easeInOutQuart'
        }
    };

    /* ========================================
       2. UTILITAIRES CHART.JS
    ======================================== */

    /**
     * Crée un dégradé linéaire pour Chart.js
     * @param {CanvasRenderingContext2D} ctx - Context du canvas
     * @param {string[]} colors - Tableau des couleurs [début, fin]
     * @param {number} height - Hauteur du graphique
     * @returns {CanvasGradient} Dégradé créé
     */
    function createGradient(ctx, colors, height = 300) {
        const gradient = ctx.createLinearGradient(0, 0, 0, height);
        gradient.addColorStop(0, colors[0]);
        gradient.addColorStop(1, colors[1]);
        return gradient;
    }

    /**
     * Crée un dégradé radial pour les graphiques en camembert
     * @param {CanvasRenderingContext2D} ctx - Context du canvas
     * @param {string[]} colors - Tableau des couleurs
     * @returns {CanvasGradient} Dégradé radial
     */
    function createRadialGradient(ctx, colors) {
        const gradient = ctx.createRadialGradient(0, 0, 0, 0, 0, 150);
        colors.forEach((color, index) => {
            gradient.addColorStop(index / (colors.length - 1), color);
        });
        return gradient;
    }

    /**
     * Formate les nombres pour l'affichage dans les tooltips
     * @param {number} value - Valeur à formater
     * @param {string} type - Type de formatage ('number', 'currency', 'percent')
     * @returns {string} Valeur formatée
     */
    function formatNumber(value, type = 'number') {
        switch (type) {
            case 'currency':
                return new Intl.NumberFormat('fr-FR', { 
                    style: 'currency', 
                    currency: 'EUR' 
                }).format(value);
            case 'percent':
                return new Intl.NumberFormat('fr-FR', { 
                    style: 'percent' 
                }).format(value / 100);
            default:
                return new Intl.NumberFormat('fr-FR').format(value);
        }
    }

    /**
     * Récupère les données des graphiques depuis le HTML
     * @returns {Object} Données des graphiques ou données par défaut
     */
    function getChartData() {
        try {
            const dataScript = document.getElementById('chartDataConfig');
            if (dataScript && dataScript.textContent) {
                return JSON.parse(dataScript.textContent);
            }
        } catch (error) {
            console.warn('Impossible de récupérer les données des graphiques:', error);
        }

        // Données par défaut si erreur
        return {
            inscriptions: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun'],
                data: [12, 19, 23, 31, 28, 45],
                objectif: [15, 20, 25, 30, 35, 40]
            },
            vehicules: {
                labels: ['Électriques', 'Thermiques'],
                data: [35, 65]
            },
            activite: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun'],
                trajets: [8, 12, 15, 22, 18, 28],
                reservations: [15, 25, 30, 45, 35, 52]
            },
            credits: {
                labels: ['0-10', '11-25', '26-50', '51-100', '100+'],
                data: [25, 35, 20, 15, 5]
            }
        };
    }

    /* ========================================
       3. GRAPHIQUE ÉVOLUTION DES INSCRIPTIONS
    ======================================== */

    /**
     * Initialise le graphique d'évolution des inscriptions (ligne)
     * @param {Object} data - Données des inscriptions
     */
    function initInscriptionsChart(data) {
        const ctx = document.getElementById('inscriptionsChart');
        if (!ctx) {
            console.warn('Canvas inscriptionsChart non trouvé');
            return;
        }

        const canvasCtx = ctx.getContext('2d');
        
        // Création des dégradés
        const primaryGradient = createGradient(canvasCtx, ADMIN_GRADIENTS.primary, 350);
        const successGradient = createGradient(canvasCtx, ADMIN_GRADIENTS.success, 350);
        
        // Dégradé de zone sous la courbe
        const areaGradient = canvasCtx.createLinearGradient(0, 0, 0, 350);
        areaGradient.addColorStop(0, 'rgba(13, 110, 253, 0.3)');
        areaGradient.addColorStop(1, 'rgba(13, 110, 253, 0.05)');

        const config = {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Inscriptions réelles',
                        data: data.data,
                        borderColor: ADMIN_COLORS.primary,
                        backgroundColor: areaGradient,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: ADMIN_COLORS.primary,
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        pointHoverBackgroundColor: ADMIN_COLORS.primary,
                        pointHoverBorderColor: '#ffffff'
                    },
                    {
                        label: 'Objectif',
                        data: data.objectif,
                        borderColor: ADMIN_COLORS.success,
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: ADMIN_COLORS.success,
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                ...DEFAULT_CHART_CONFIG,
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                weight: 'bold'
                            },
                            color: ADMIN_COLORS.secondary
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return formatNumber(value);
                            },
                            color: ADMIN_COLORS.secondary
                        }
                    }
                },
                plugins: {
                    ...DEFAULT_CHART_CONFIG.plugins,
                    tooltip: {
                        ...DEFAULT_CHART_CONFIG.plugins.tooltip,
                        callbacks: {
                            title: function(context) {
                                return 'Mois de ' + context[0].label;
                            },
                            label: function(context) {
                                const label = context.dataset.label || '';
                                const value = formatNumber(context.parsed.y);
                                return label + ': ' + value + ' inscriptions';
                            }
                        }
                    }
                }
            }
        };

        // Création du graphique avec gestion d'erreur
        try {
            window.inscriptionsChart = new Chart(canvasCtx, config);
            console.log('✅ Graphique inscriptions initialisé');
        } catch (error) {
            console.error('❌ Erreur lors de l\'initialisation du graphique inscriptions:', error);
        }
    }

    /* ========================================
       4. GRAPHIQUE TYPES DE VÉHICULES
    ======================================== */

    /**
     * Initialise le graphique des types de véhicules (camembert)
     * @param {Object} data - Données des véhicules
     */
    function initVehiculesChart(data) {
        const ctx = document.getElementById('vehiculesChart');
        if (!ctx) {
            console.warn('Canvas vehiculesChart non trouvé');
            return;
        }

        const canvasCtx = ctx.getContext('2d');
        
        const config = {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.data,
                    backgroundColor: [
                        ADMIN_COLORS.success,
                        ADMIN_COLORS.secondary
                    ],
                    borderColor: [
                        '#ffffff',
                        '#ffffff'
                    ],
                    borderWidth: 3,
                    hoverOffset: 10
                }]
            },
            options: {
                ...DEFAULT_CHART_CONFIG,
                cutout: '60%', // Taille du trou central
                plugins: {
                    ...DEFAULT_CHART_CONFIG.plugins,
                    tooltip: {
                        ...DEFAULT_CHART_CONFIG.plugins.tooltip,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return label + ': ' + value + ' véhicules (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        };

        try {
            window.vehiculesChart = new Chart(canvasCtx, config);
            console.log('✅ Graphique véhicules initialisé');
        } catch (error) {
            console.error('❌ Erreur lors de l\'initialisation du graphique véhicules:', error);
        }
    }

    /* ========================================
       5. GRAPHIQUE ACTIVITÉ MENSUELLE
    ======================================== */

    /**
     * Initialise le graphique d'activité mensuelle (barres)
     * @param {Object} data - Données d'activité
     */
    function initActiviteChart(data) {
        const ctx = document.getElementById('activiteChart');
        if (!ctx) {
            console.warn('Canvas activiteChart non trouvé');
            return;
        }

        const canvasCtx = ctx.getContext('2d');

        const config = {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Trajets proposés',
                        data: data.trajets,
                        backgroundColor: ADMIN_COLORS.success,
                        borderColor: ADMIN_COLORS.success,
                        borderWidth: 1,
                        borderRadius: 4,
                        borderSkipped: false
                    },
                    {
                        label: 'Réservations',
                        data: data.reservations,
                        backgroundColor: ADMIN_COLORS.info,
                        borderColor: ADMIN_COLORS.info,
                        borderWidth: 1,
                        borderRadius: 4,
                        borderSkipped: false
                    }
                ]
            },
            options: {
                ...DEFAULT_CHART_CONFIG,
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: ADMIN_COLORS.secondary
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return formatNumber(value);
                            },
                            color: ADMIN_COLORS.secondary
                        }
                    }
                },
                plugins: {
                    ...DEFAULT_CHART_CONFIG.plugins,
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        ...DEFAULT_CHART_CONFIG.plugins.tooltip,
                        callbacks: {
                            title: function(context) {
                                return 'Mois de ' + context[0].label;
                            },
                            label: function(context) {
                                const label = context.dataset.label || '';
                                const value = formatNumber(context.parsed.y);
                                return label + ': ' + value;
                            }
                        }
                    }
                }
            }
        };

        try {
            window.activiteChart = new Chart(canvasCtx, config);
            console.log('✅ Graphique activité initialisé');
        } catch (error) {
            console.error('❌ Erreur lors de l\'initialisation du graphique activité:', error);
        }
    }

    /* ========================================
       6. GRAPHIQUE DISTRIBUTION CRÉDITS
    ======================================== */

    /**
     * Initialise le graphique de distribution des crédits (aires)
     * @param {Object} data - Données des crédits
     */
    function initCreditsChart(data) {
        const ctx = document.getElementById('creditsChart');
        if (!ctx) {
            console.warn('Canvas creditsChart non trouvé');
            return;
        }

        const canvasCtx = ctx.getContext('2d');

        // Dégradé pour les aires
        const areaGradient = canvasCtx.createLinearGradient(0, 0, 0, 280);
        areaGradient.addColorStop(0, 'rgba(255, 193, 7, 0.3)');
        areaGradient.addColorStop(1, 'rgba(255, 193, 7, 0.05)');

        const config = {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Pourcentage d\'utilisateurs',
                    data: data.data,
                    backgroundColor: areaGradient,
                    borderColor: ADMIN_COLORS.warning,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: ADMIN_COLORS.warning,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                ...DEFAULT_CHART_CONFIG,
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: ADMIN_COLORS.secondary
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: 40,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            },
                            color: ADMIN_COLORS.secondary
                        }
                    }
                },
                plugins: {
                    ...DEFAULT_CHART_CONFIG.plugins,
                    tooltip: {
                        ...DEFAULT_CHART_CONFIG.plugins.tooltip,
                        callbacks: {
                            title: function(context) {
                                return 'Tranche: ' + context[0].label + ' crédits';
                            },
                            label: function(context) {
                                const value = context.parsed.y;
                                return 'Représente ' + value + '% des utilisateurs';
                            }
                        }
                    }
                }
            }
        };

        try {
            window.creditsChart = new Chart(canvasCtx, config);
            console.log('✅ Graphique crédits initialisé');
        } catch (error) {
            console.error('❌ Erreur lors de l\'initialisation du graphique crédits:', error);
        }
    }

    /* ========================================
       7. CONTRÔLES ET INTERACTIONS
    ======================================== */

    /**
     * Initialise les contrôles des graphiques (boutons de période, etc.)
     */
    function initChartControls() {
        // Contrôles de période pour le graphique des inscriptions
        const periodButtons = document.querySelectorAll('[data-chart-period]');
        
        periodButtons.forEach(button => {
            button.addEventListener('click', function() {
                const period = this.getAttribute('data-chart-period');
                
                // Mise à jour de l'état actif des boutons
                periodButtons.forEach(btn => {
                    btn.classList.remove('chart-control-btn--active');
                    btn.setAttribute('aria-pressed', 'false');
                });
                
                this.classList.add('chart-control-btn--active');
                this.setAttribute('aria-pressed', 'true');
                
                // Mise à jour du graphique selon la période
                updateInscriptionsChartPeriod(period);
            });
        });
    }

    /**
     * Met à jour le graphique des inscriptions selon la période sélectionnée
     * @param {string} period - Période en mois ('3', '6', '12')
     */
    function updateInscriptionsChartPeriod(period) {
        if (!window.inscriptionsChart) return;

        const data = getChartData();
        let newData = data.inscriptions;

        // Simulation de données différentes selon la période
        // En production, ces données viendraient d'une API
        switch (period) {
            case '3':
                newData = {
                    labels: ['Avr', 'Mai', 'Jun'],
                    data: [31, 28, 45],
                    objectif: [30, 35, 40]
                };
                break;
            case '6':
                newData = {
                    labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun'],
                    data: data.inscriptions.data,
                    objectif: data.inscriptions.objectif
                };
                break;
            case '12':
            default:
                newData = data.inscriptions;
                break;
        }

        // Mise à jour des données avec animation
        window.inscriptionsChart.data.labels = newData.labels;
        window.inscriptionsChart.data.datasets[0].data = newData.data;
        window.inscriptionsChart.data.datasets[1].data = newData.objectif;
        window.inscriptionsChart.update('active');

        console.log(`📊 Graphique inscriptions mis à jour pour ${period} mois`);
    }

    /* ========================================
       8. ANIMATIONS AVANCÉES
    ======================================== */

    /**
     * Anime les compteurs numériques des cartes métriques
     */
    function animateCounters() {
        const counters = document.querySelectorAll('[data-counter]');
        
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-counter'));
            const duration = 1000; // 1 seconde
            const start = 0;
            const increment = target / (duration / 16); // 60fps
            let current = start;

            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                
                counter.textContent = Math.floor(current).toLocaleString('fr-FR');
            }, 16);
        });
    }

    /**
     * Anime les barres de progression
     */
    function animateProgressBars() {
        const progressBars = document.querySelectorAll('.progress-fill');
        
        progressBars.forEach((bar, index) => {
            setTimeout(() => {
                bar.style.opacity = '0';
                bar.style.width = '0%';
                
                setTimeout(() => {
                    bar.style.transition = 'all 0.8s ease';
                    bar.style.opacity = '1';
                    
                    // Récupération de la largeur finale depuis le CSS
                    const finalWidth = getComputedStyle(bar).width;
                    bar.style.width = finalWidth;
                }, 100);
            }, index * 200);
        });
    }

    /* ========================================
       9. RESPONSIVE ET REDIMENSIONNEMENT
    ======================================== */

    /**
     * Gère le redimensionnement responsive des graphiques
     */
    function handleResize() {
        const charts = [
            window.inscriptionsChart,
            window.vehiculesChart,
            window.activiteChart,
            window.creditsChart
        ];

        charts.forEach(chart => {
            if (chart && typeof chart.resize === 'function') {
                chart.resize();
            }
        });
    }

    /* ========================================
       10. INITIALISATION PRINCIPALE
    ======================================== */

    /**
     * Fonction principale d'initialisation des graphiques
     */
    function initializeAdminCharts() {
        console.log('🚀 Initialisation des graphiques admin EcoRide...');

        // Vérification de la disponibilité de Chart.js
        if (typeof Chart === 'undefined') {
            console.error('❌ Chart.js n\'est pas chargé');
            return;
        }

        // Récupération des données
        const chartData = getChartData();
        console.log('📊 Données des graphiques récupérées:', chartData);

        // Initialisation de chaque graphique
        try {
            initInscriptionsChart(chartData.inscriptions);
            initVehiculesChart(chartData.vehicules);
            initActiviteChart(chartData.activite);
            initCreditsChart(chartData.credits);

            // Initialisation des contrôles
            initChartControls();

            // Animations
            setTimeout(() => {
                animateCounters();
                animateProgressBars();
            }, 500);

            console.log('✅ Tous les graphiques admin ont été initialisés avec succès');
        } catch (error) {
            console.error('❌ Erreur lors de l\'initialisation des graphiques:', error);
        }
    }

    /**
     * Fonction de nettoyage lors du changement de page
     */
    function destroyAdminCharts() {
        const charts = [
            window.inscriptionsChart,
            window.vehiculesChart,
            window.activiteChart,
            window.creditsChart
        ];

        charts.forEach(chart => {
            if (chart && typeof chart.destroy === 'function') {
                chart.destroy();
            }
        });

        console.log('🧹 Graphiques admin nettoyés');
    }

    /* ========================================
       11. ÉVÉNEMENTS ET DÉMARRAGE
    ======================================== */

    // Attendre que le DOM soit complètement chargé
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeAdminCharts);
    } else {
        initializeAdminCharts();
    }

    // Gestion du redimensionnement
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(handleResize, 250);
    });

    // Nettoyage lors du déchargement de la page
    window.addEventListener('beforeunload', destroyAdminCharts);

    // Exposition des fonctions globales pour debug et tests
    window.AdminCharts = {
        init: initializeAdminCharts,
        destroy: destroyAdminCharts,
        updatePeriod: updateInscriptionsChartPeriod,
        colors: ADMIN_COLORS
    };

})();

