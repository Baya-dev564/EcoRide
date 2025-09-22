/**
 * JavaScript unifié pour l'interface d'administration EcoRide
 * Version fusionnée : Chart.js (existant) + nouvelles fonctionnalités utilisateurs
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
       2. NOUVEAU : DÉTECTION DE PAGE ET ÉTAT
    ======================================== */

    // État global de l'application admin
    let adminApp = {
        currentPage: '',
        charts: {},
        cache: {}
    };

    /**
     * Détecte la page admin actuelle pour initialiser les bonnes fonctionnalités
     */
    function detectCurrentPage() {
        const url = window.location.pathname;
        const body = document.body;
        
        if (url.includes('/dashboard') || body.querySelector('#inscriptionsChart')) {
            return 'dashboard';
        } else if (url.includes('/user-stats') || body.querySelector('#evolutionChart')) {
            return 'utilisateurs-stat';
        } else if (url.includes('/user-edit') || body.querySelector('#editUserForm')) {
            return 'utilisateurs-edit';
        } else if (url.includes('/utilisateurs') || body.querySelector('#usersTable')) {
            return 'utilisateurs';
        } else if (url.includes('/trajets')) {
            return 'trajets';
        } else if (url.includes('/avis')) {
            return 'avis';
        }
        
        return 'general';
    }

    /* ========================================
       3. UTILITAIRES CHART.JS (TES FONCTIONS EXISTANTES)
    ======================================== */

    /**
     * Crée un dégradé linéaire pour Chart.js
     */
    function createGradient(ctx, colors, height = 300) {
        const gradient = ctx.createLinearGradient(0, 0, 0, height);
        gradient.addColorStop(0, colors[0]);
        gradient.addColorStop(1, colors[1]);
        return gradient;
    }

    /**
     * Crée un dégradé radial pour les graphiques en camembert
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
       4. TES GRAPHIQUES EXISTANTS (DASHBOARD)
    ======================================== */

    /**
     * Initialise le graphique d'évolution des inscriptions (ligne)
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

        try {
            adminApp.charts.inscriptions = new Chart(canvasCtx, config);
            console.log('✅ Graphique inscriptions initialisé');
        } catch (error) {
            console.error('❌ Erreur lors de l\'initialisation du graphique inscriptions:', error);
        }
    }

    /**
     * Initialise le graphique des types de véhicules (camembert)
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
                cutout: '60%',
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
            adminApp.charts.vehicules = new Chart(canvasCtx, config);
            console.log('✅ Graphique véhicules initialisé');
        } catch (error) {
            console.error('❌ Erreur lors de l\'initialisation du graphique véhicules:', error);
        }
    }

    /**
     * Initialise le graphique d'activité mensuelle (barres)
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
            adminApp.charts.activite = new Chart(canvasCtx, config);
            console.log('✅ Graphique activité initialisé');
        } catch (error) {
            console.error('❌ Erreur lors de l\'initialisation du graphique activité:', error);
        }
    }

    /**
     * Initialise le graphique de distribution des crédits (aires)
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
            adminApp.charts.credits = new Chart(canvasCtx, config);
            console.log('✅ Graphique crédits initialisé');
        } catch (error) {
            console.error('❌ Erreur lors de l\'initialisation du graphique crédits:', error);
        }
    }

    /* ========================================
       5. TES CONTRÔLES EXISTANTS
    ======================================== */

    /**
     * Initialise les contrôles des graphiques (boutons de période, etc.)
     */
    function initChartControls() {
        const periodButtons = document.querySelectorAll('[data-chart-period]');
        
        periodButtons.forEach(button => {
            button.addEventListener('click', function() {
                const period = this.getAttribute('data-chart-period');
                
                periodButtons.forEach(btn => {
                    btn.classList.remove('chart-control-btn--active');
                    btn.setAttribute('aria-pressed', 'false');
                });
                
                this.classList.add('chart-control-btn--active');
                this.setAttribute('aria-pressed', 'true');
                
                updateInscriptionsChartPeriod(period);
            });
        });
    }

    /**
     * Met à jour le graphique des inscriptions selon la période sélectionnée
     */
    function updateInscriptionsChartPeriod(period) {
        if (!adminApp.charts.inscriptions) return;

        const data = getChartData();
        let newData = data.inscriptions;

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

        adminApp.charts.inscriptions.data.labels = newData.labels;
        adminApp.charts.inscriptions.data.datasets[0].data = newData.data;
        adminApp.charts.inscriptions.data.datasets[1].data = newData.objectif;
        adminApp.charts.inscriptions.update('active');

        console.log(`📊 Graphique inscriptions mis à jour pour ${period} mois`);
    }

    /* ========================================
       6. TES ANIMATIONS EXISTANTES
    ======================================== */

    /**
     * Anime les compteurs numériques des cartes métriques
     */
    function animateCounters() {
        const counters = document.querySelectorAll('[data-counter]');
        
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-counter'));
            const duration = 1000;
            const start = 0;
            const increment = target / (duration / 16);
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
                    
                    const finalWidth = getComputedStyle(bar).width;
                    bar.style.width = finalWidth;
                }, 100);
            }, index * 200);
        });
    }

    /* ========================================
       7. NOUVEAU : FONCTIONNALITÉS UTILISATEURS
    ======================================== */

    /**
     * Initialise la page utilisateurs (recherche, filtres, actions)
     */
    function initUtilisateurs() {
        console.log('👥 Initialisation Utilisateurs');
        
        initUserSearch();
        initUserFilters();
        initUserActions();
        initEditCreditsModal();
    }

    /**
     * Initialise la recherche d'utilisateurs en temps réel
     */
    function initUserSearch() {
        const searchInput = document.getElementById('searchUsers');
        if (!searchInput) return;

        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.toLowerCase().trim();
            
            searchTimeout = setTimeout(() => {
                filterUsers(query);
            }, 300);
        });
    }

    /**
     * Initialise les filtres d'utilisateurs
     */
    function initUserFilters() {
        const roleFilter = document.getElementById('filterRole');
        if (!roleFilter) return;
        
        roleFilter.addEventListener('change', function() {
            const selectedRole = this.value;
            filterUsersByRole(selectedRole);
        });
    }

    /**
     * Filtre les utilisateurs par recherche textuelle
     */
    function filterUsers(query) {
        const rows = document.querySelectorAll('#usersTable tbody tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const pseudo = row.querySelector('.user-pseudo')?.textContent.toLowerCase() || '';
            const email = row.querySelector('.user-email')?.textContent.toLowerCase() || '';
            const name = row.querySelector('.user-name')?.textContent.toLowerCase() || '';
            
            const isVisible = query === '' || 
                             pseudo.includes(query) || 
                             email.includes(query) || 
                             name.includes(query);
            
            row.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount++;
        });
        
        updateUserCount(visibleCount);
    }

    /**
     * Filtre les utilisateurs par rôle
     */
    function filterUsersByRole(role) {
        const rows = document.querySelectorAll('#usersTable tbody tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const userRole = row.getAttribute('data-role');
            const isVisible = role === '' || userRole === role;
            
            row.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount++;
        });
        
        updateUserCount(visibleCount);
    }

    /**
     * Met à jour le compteur d'utilisateurs affichés
     */
    function updateUserCount(count) {
        const badge = document.querySelector('.stat-badge-primary');
        if (badge) {
            badge.innerHTML = `<i class="fas fa-user-friends me-1" aria-hidden="true"></i> ${count} utilisateurs`;
        }
    }

    /**
     * Initialise les actions sur les utilisateurs
     */
    function initUserActions() {
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-view-stats')) {
                const btn = e.target.closest('.btn-view-stats');
                const userId = btn.getAttribute('data-user-id');
                viewUserStats(userId);
            }
        });
    }

    /**
     * Redirige vers les statistiques d'un utilisateur
     */
    function viewUserStats(userId) {
        window.location.href = `/admin/user-stats/${userId}`;
    }

    /**
     * Initialise le modal de modification des crédits
     */
    function initEditCreditsModal() {
        const modal = document.getElementById('editCreditsModal');
        const form = document.getElementById('editCreditsForm');
        const saveBtn = document.getElementById('saveCreditsBtn');
        
        if (!modal || !form || !saveBtn) return;
        
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-edit-credits')) {
                const btn = e.target.closest('.btn-edit-credits');
                const userId = btn.getAttribute('data-user-id');
                const pseudo = btn.getAttribute('data-user-pseudo');
                const currentCredits = btn.getAttribute('data-current-credits');
                
                document.getElementById('editUserId').value = userId;
                document.getElementById('editUserPseudo').value = pseudo;
                document.getElementById('editCurrentCredits').value = currentCredits;
                document.getElementById('editNewCredits').value = currentCredits;
                
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            }
        });
        
        saveBtn.addEventListener('click', function() {
            saveUserCredits();
        });
    }

    /**
     * Sauvegarde les modifications de crédits via AJAX
     */
    function saveUserCredits() {
        const userId = document.getElementById('editUserId').value;
        const newCredits = document.getElementById('editNewCredits').value;
        
        if (!userId || !newCredits) {
            showAlert('Erreur : données manquantes', 'danger');
            return;
        }
        
        if (newCredits < 0 || newCredits > 1000) {
            showAlert('Erreur : les crédits doivent être entre 0 et 1000', 'danger');
            return;
        }
        
        const saveBtn = document.getElementById('saveCreditsBtn');
        const originalText = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...';
        
        fetch('/admin/modifier-credits', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                user_id: userId,
                nouveaux_credits: parseInt(newCredits)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                updateUserCreditsInTable(userId, newCredits);
                
                const modal = bootstrap.Modal.getInstance(document.getElementById('editCreditsModal'));
                modal.hide();
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Erreur AJAX:', error);
            showAlert('Erreur de communication avec le serveur', 'danger');
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        });
    }

    /**
     * Met à jour l'affichage des crédits dans le tableau
     */
    function updateUserCreditsInTable(userId, newCredits) {
        const row = document.querySelector(`[data-user-id="${userId}"]`);
        if (row) {
            const creditBadge = row.querySelector('.credit-badge');
            if (creditBadge) {
                creditBadge.innerHTML = `<i class="fas fa-coins me-1" aria-hidden="true"></i> ${newCredits}`;
                creditBadge.className = `credit-badge credit-badge-${newCredits >= 10 ? 'success' : 'warning'}`;
            }
        }
    }

    /* ========================================
       8. NOUVEAU : STATISTIQUES UTILISATEUR
    ======================================== */

    /**
     * Initialise la page des statistiques utilisateur
     */
    function initUtilisateursStats() {
        console.log('📈 Initialisation Statistiques Utilisateur');
        loadUserStatsChart();
    }

    /**
     * Charge le graphique d'évolution des statistiques utilisateur
     */
    function loadUserStatsChart() {
        const chartScript = document.getElementById('chart-data');
        const chartCanvas = document.getElementById('evolutionChart');
        
        if (!chartScript || !chartCanvas) {
            console.warn('Données de graphique utilisateur non trouvées');
            return;
        }
        
        try {
            const data = JSON.parse(chartScript.textContent);
            createEvolutionChart(chartCanvas, data.evolution);
        } catch (error) {
            console.error('Erreur parsing données graphique utilisateur:', error);
        }
    }

    /**
     * Crée le graphique d'évolution utilisateur avec Chart.js
     */
    function createEvolutionChart(canvas, evolutionData) {
        const moisNoms = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 
                          'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        
        const trajetsData = evolutionData.trajets || [];
        const reservationsData = evolutionData.reservations || [];
        
        if (trajetsData.length === 0 && reservationsData.length === 0) {
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#f8f9fa';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#6c757d';
            ctx.textAlign = 'center';
            ctx.fillText('Aucune donnée d\'activité disponible', canvas.width/2, canvas.height/2);
            return;
        }
        
        const labels = [];
        const trajetsValues = [];
        const reservationsValues = [];
        
        const currentDate = new Date();
        for (let i = 11; i >= 0; i--) {
            const date = new Date(currentDate.getFullYear(), currentDate.getMonth() - i, 1);
            const mois = date.getMonth() + 1;
            const annee = date.getFullYear();
            const moisKey = `${annee}-${mois.toString().padStart(2, '0')}`;
            
            labels.push(moisNoms[date.getMonth()]);
            
            const trajetData = trajetsData.find(t => t.mois === moisKey);
            const reservationData = reservationsData.find(r => r.mois === moisKey);
            
            trajetsValues.push(trajetData ? parseInt(trajetData.nb_trajets) : 0);
            reservationsValues.push(reservationData ? parseInt(reservationData.nb_reservations) : 0);
        }
        
        adminApp.charts.evolution = new Chart(canvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Trajets proposés',
                    data: trajetsValues,
                    borderColor: ADMIN_COLORS.primary,
                    backgroundColor: ADMIN_COLORS.primary + '20',
                    tension: 0.4
                }, {
                    label: 'Réservations effectuées',
                    data: reservationsValues,
                    borderColor: ADMIN_COLORS.success,
                    backgroundColor: ADMIN_COLORS.success + '20',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    /* ========================================
       9. NOUVEAU : MODIFICATION UTILISATEUR
    ======================================== */

    /**
     * Initialise la page de modification utilisateur
     */
    function initUtilisateursEdit() {
        console.log('✏️ Initialisation Modification Utilisateur');
        initUserEditValidation();
    }

    /**
     * Initialise la validation du formulaire de modification
     */
    function initUserEditValidation() {
        const form = document.getElementById('editUserForm');
        if (!form) return;
        
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (validateForm(form)) {
                submitUserForm(form);
            }
        });
    }

    /**
     * Valide un champ spécifique
     */
    function validateField(field) {
        const fieldName = field.name;
        const value = field.value.trim();
        let isValid = true;
        let errorMessage = '';
        
        switch(fieldName) {
            case 'pseudo':
                if (value.length < 3 || value.length > 50) {
                    isValid = false;
                    errorMessage = 'Le pseudo doit faire entre 3 et 50 caractères';
                }
                break;
                
            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    isValid = false;
                    errorMessage = 'Format d\'email invalide';
                }
                break;
                
            case 'credit':
                const credits = parseInt(value);
                if (isNaN(credits) || credits < 0 || credits > 9999) {
                    isValid = false;
                    errorMessage = 'Les crédits doivent être entre 0 et 9999';
                }
                break;
                
            case 'code_postal':
                if (value && !/^[0-9]{5}$/.test(value)) {
                    isValid = false;
                    errorMessage = 'Le code postal doit faire 5 chiffres';
                }
                break;
        }
        
        if (isValid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            
            const errorElement = document.getElementById(fieldName + 'Error');
            if (errorElement) {
                errorElement.textContent = errorMessage;
            }
        }
        
        return isValid;
    }

    /**
     * Supprime les erreurs d'un champ
     */
    function clearFieldError(field) {
        field.classList.remove('is-invalid', 'is-valid');
    }

    /**
     * Valide l'ensemble du formulaire
     */
    function validateForm(form) {
        const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
        let isValid = true;
        
        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    }

    /**
     * Soumet le formulaire de modification
     */
    function submitUserForm(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...';
        
        form.submit();
    }

    /* ========================================
       10. NOUVEAU : UTILITAIRES GÉNÉRAUX
    ======================================== */

    /**
     * Affiche une alerte Bootstrap dynamique
     */
    function showAlert(message, type = 'info') {
        const alertContainer = document.querySelector('.container') || document.body;
        const alertDiv = document.createElement('div');
        
        alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-3`;
        alertDiv.innerHTML = `
            <i class="fas fa-${getAlertIcon(type)} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        alertContainer.insertBefore(alertDiv, alertContainer.firstChild);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                const bsAlert = new bootstrap.Alert(alertDiv);
                bsAlert.close();
            }
        }, 5000);
    }

    /**
     * Retourne l'icône appropriée pour le type d'alerte
     */
    function getAlertIcon(type) {
        switch(type) {
            case 'success': return 'check-circle';
            case 'danger': return 'exclamation-triangle';
            case 'warning': return 'exclamation-circle';
            case 'info': return 'info-circle';
            default: return 'info-circle';
        }
    }

    /* ========================================
       11. GESTION DU REDIMENSIONNEMENT
    ======================================== */

    /**
     * Gère le redimensionnement responsive des graphiques
     */
    function handleResize() {
        Object.values(adminApp.charts).forEach(chart => {
            if (chart && typeof chart.resize === 'function') {
                chart.resize();
            }
        });
    }

    /* ========================================
       12. INITIALISATION PRINCIPALE FUSIONNÉE
    ======================================== */

    /**
     * Fonction principale d'initialisation selon la page
     */
    function initializeAdmin() {
        console.log('🚀 Initialisation Admin EcoRide - Version fusionnée');
        
        // Détection de la page actuelle
        adminApp.currentPage = detectCurrentPage();
        console.log('📍 Page détectée:', adminApp.currentPage);
        
        // Initialisation selon la page
        switch(adminApp.currentPage) {
            case 'dashboard':
                initializeDashboard();
                break;
            case 'utilisateurs':
                initUtilisateurs();
                break;
            case 'utilisateurs-stat':
                initUtilisateursStats();
                break;
            case 'utilisateurs-edit':
                initUtilisateursEdit();
                break;
            default:
                console.log('🔧 Page générique - fonctionnalités de base uniquement');
        }
        
        // Initialisation des composants communs
        initCommonComponents();
    }

    /**
     * Initialise le dashboard avec tes graphiques existants
     */
    function initializeDashboard() {
        console.log('📊 Initialisation Dashboard avec graphiques Chart.js');
        
        if (typeof Chart === 'undefined') {
            console.error('❌ Chart.js n\'est pas chargé');
            return;
        }

        const chartData = getChartData();
        console.log('📊 Données des graphiques récupérées:', chartData);

        try {
            initInscriptionsChart(chartData.inscriptions);
            initVehiculesChart(chartData.vehicules);
            initActiviteChart(chartData.activite);
            initCreditsChart(chartData.credits);

            initChartControls();

            setTimeout(() => {
                animateCounters();
                animateProgressBars();
            }, 500);

            console.log('✅ Dashboard initialisé avec succès');
        } catch (error) {
            console.error('❌ Erreur lors de l\'initialisation du dashboard:', error);
        }
    }

    /**
     * Initialise les composants communs à toutes les pages
     */
    function initCommonComponents() {
        // Auto-dismiss des alertes
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert && alert.parentNode) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        });

        // Tooltips Bootstrap
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    /**
     * Fonction de nettoyage lors du changement de page
     */
    function destroyAdminCharts() {
        Object.values(adminApp.charts).forEach(chart => {
            if (chart && typeof chart.destroy === 'function') {
                chart.destroy();
            }
        });
        adminApp.charts = {};
        console.log('🧹 Graphiques admin nettoyés');
    }

    /* ========================================
       13. ÉVÉNEMENTS ET DÉMARRAGE
    ======================================== */

    // Démarrage selon l'état du DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeAdmin);
    } else {
        initializeAdmin();
    }

    // Gestion du redimensionnement
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(handleResize, 250);
    });

    // Nettoyage avant déchargement
    window.addEventListener('beforeunload', destroyAdminCharts);

    // Exposition globale pour debug et compatibilité
    window.AdminCharts = {
        init: initializeAdmin,
        destroy: destroyAdminCharts,
        updatePeriod: updateInscriptionsChartPeriod,
        colors: ADMIN_COLORS,
        app: adminApp
    };

    console.log('✅ Admin.js fusionné chargé et prêt');

})();
