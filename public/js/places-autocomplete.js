/**
 * Système d'autocomplete pour les points de rendez-vous EcoRide
 * J'utilise l'API Nominatim via notre service PHP
 */

class PlacesAutocomplete {
    constructor(inputElement, options = {}) {
        this.input = inputElement;
        this.options = {
            minLength: 2,
            delay: 300,
            maxResults: 8,
            apiUrl: '/api/places/search.php',
            onSelect: () => {},
            ...options
        };
        
        this.timeout = null;
        this.isOpen = false;
        
        this.init();
    }
    
    init() {
        // Je crée le conteneur de suggestions
        this.createSuggestionContainer();
        
        // J'écoute les événements
        this.bindEvents();
        
        console.log('Autocomplete initialisé pour', this.input.id);
    }
    
    createSuggestionContainer() {
        // Je crée la liste des suggestions
        this.suggestionList = document.createElement('div');
        this.suggestionList.className = 'places-suggestions';
        this.suggestionList.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        `;
        
        // Je positionne l'input en relatif
        this.input.style.position = 'relative';
        this.input.parentNode.style.position = 'relative';
        this.input.parentNode.appendChild(this.suggestionList);
    }
    
    bindEvents() {
        // Je recherche au clavier
        this.input.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            console.log('Input event:', query);
            
            if (query.length >= this.options.minLength) {
                this.scheduleSearch(query);
            } else {
                this.hideSuggestions();
            }
        });
        
        // Je navigue au clavier
        this.input.addEventListener('keydown', (e) => {
            this.handleKeyNavigation(e);
        });
        
        // Je ferme au clic extérieur
        document.addEventListener('click', (e) => {
            if (!this.input.contains(e.target) && !this.suggestionList.contains(e.target)) {
                this.hideSuggestions();
            }
        });
    }
    
    scheduleSearch(query) {
        console.log('Programmation recherche pour:', query);
        
        // Je nettoie le timeout précédent
        if (this.timeout) {
            clearTimeout(this.timeout);
        }
        
        // Je programme une nouvelle recherche
        this.timeout = setTimeout(() => {
            this.searchPlaces(query);
        }, this.options.delay);
    }
    
    async searchPlaces(query) {
        try {
            console.log('Recherche lancée:', query);
            
            // Je construis l'URL de recherche
            const params = new URLSearchParams({
                q: query,
                type: this.options.type || '',
                city: this.options.city || ''
            });
            
            const url = `${this.options.apiUrl}?${params}`;
            console.log('URL finale:', url);
            
            const response = await fetch(url);
            console.log('Réponse HTTP status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const places = await response.json();
            
            console.log('Places trouvées:', places.length, places);
            this.showSuggestions(places);
            
        } catch (error) {
            console.error('Erreur recherche places:', error);
            this.showError('Erreur de connexion');
        }
    }
    
    showSuggestions(places) {
        console.log('Affichage suggestions:', places.length);
        
        // Je vide la liste
        this.suggestionList.innerHTML = '';
        
        if (places.length === 0) {
            this.suggestionList.innerHTML = '<div class="suggestion-item no-results">Aucun lieu trouvé</div>';
        } else {
            places.forEach((place, index) => {
                const item = this.createSuggestionItem(place, index);
                this.suggestionList.appendChild(item);
            });
        }
        
        // J'affiche les suggestions
        this.suggestionList.style.display = 'block';
        this.isOpen = true;
        
        console.log('Suggestions affichées');
    }
    
    showError(message) {
        this.suggestionList.innerHTML = `<div class="suggestion-item no-results">Erreur: ${message}</div>`;
        this.suggestionList.style.display = 'block';
        this.isOpen = true;
    }
    
    createSuggestionItem(place, index) {
        const item = document.createElement('div');
        item.className = 'suggestion-item';
        item.dataset.index = index;
        item.dataset.placeId = place.id;
        
        item.style.cssText = `
            padding: 12px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background-color 0.2s;
        `;
        
        item.innerHTML = `
            <span class="place-icon" style="font-size: 18px;">${place.icon}</span>
            <div class="place-info" style="flex: 1;">
                <div class="place-name" style="font-weight: 500; color: #333;">
                    ${place.name}
                </div>
                <div class="place-address" style="font-size: 12px; color: #666;">
                    ${place.city ? place.city : ''} ${place.type !== 'autre' ? '• ' + this.getTypeLabel(place.type) : ''}
                </div>
            </div>
        `;
        
        // J'écoute les événements
        item.addEventListener('click', () => {
            console.log('Clic sur item:', place.name);
            this.selectPlace(place);
        });
        
        item.addEventListener('mouseenter', () => {
            this.highlightItem(item);
        });
        
        return item;
    }
    
    selectPlace(place) {
        // Je remplis l'input
        this.input.value = place.name;
        
        // Je stocke les données du lieu
        this.input.dataset.placeId = place.id;
        this.input.dataset.latitude = place.latitude;
        this.input.dataset.longitude = place.longitude;
        this.input.dataset.placeType = place.type;
        
        // Je ferme les suggestions
        this.hideSuggestions();
        
        // Callback personnalisé
        this.options.onSelect(place, this.input);
        
        console.log('Lieu sélectionné:', place.name, '- GPS:', place.latitude, place.longitude);
    }
    
    getTypeLabel(type) {
        const labels = {
            'gare': 'Gare',
            'parking': 'Parking',
            'centre_commercial': 'Centre commercial',
            'universite': 'Université',
            'autre': 'Autre lieu'
        };
        return labels[type] || 'Lieu';
    }
    
    hideSuggestions() {
        this.suggestionList.style.display = 'none';
        this.isOpen = false;
        console.log('Suggestions masquées');
    }
    
    highlightItem(item) {
        // Je retire l'ancien highlight
        const highlighted = this.suggestionList.querySelector('.highlighted');
        if (highlighted) {
            highlighted.classList.remove('highlighted');
            highlighted.style.backgroundColor = '';
        }
        
        // J'ajoute le nouveau
        item.classList.add('highlighted');
        item.style.backgroundColor = '#f8f9fa';
    }
    
    handleKeyNavigation(e) {
        if (!this.isOpen) return;
        
        const items = this.suggestionList.querySelectorAll('.suggestion-item');
        const highlighted = this.suggestionList.querySelector('.highlighted');
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                const nextItem = highlighted?.nextElementSibling || items[0];
                if (nextItem) this.highlightItem(nextItem);
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                const prevItem = highlighted?.previousElementSibling || items[items.length - 1];
                if (prevItem) this.highlightItem(prevItem);
                break;
                
            case 'Enter':
                e.preventDefault();
                if (highlighted && highlighted.dataset.placeId) {
                    highlighted.click();
                }
                break;
                
            case 'Escape':
                this.hideSuggestions();
                break;
        }
    }
}

// Je crée le style CSS pour l'autocomplete
const style = document.createElement('style');
style.textContent = `
    .suggestion-item:hover {
        background-color: #f8f9fa !important;
    }
    .suggestion-item.no-results {
        padding: 15px;
        text-align: center;
        color: #666;
        font-style: italic;
    }
    .places-suggestions {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
`;
document.head.appendChild(style);

console.log('PlacesAutocomplete class chargée avec debug !');
