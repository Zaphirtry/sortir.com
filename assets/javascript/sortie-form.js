export const FormHandler = {
    // Cache pour stocker les résultats des requêtes
    cache: {
        lieux: new Map(),
        lieuDetails: new Map()
    },

    init(selectors) {
        // Utilisation de la déstructuration pour plus de clarté
        const elements = ['villeSelect', 'lieuSelect', 'rueInput', 'codePostalInput', 'latitudeInput', 'longitudeInput']
            .reduce((acc, key) => {
                const element = document.querySelector(selectors[key]);
                if (!element) console.error(`${key} non trouvé`);
                acc[key] = element;
                return acc;
            }, {});

        Object.assign(this, elements);

        this.baseUrl = window.location.origin;
        this.setupEventListeners();
        
        if (this.villeSelect.value) {
            this.initializeExistingData();
        } else {
            this.disableLieuSelect();
        }
    },

    setupEventListeners() {
        // Utilisation de la délégation d'événements
        this.villeSelect.addEventListener('change', this.handleVilleChange.bind(this));
        this.lieuSelect.addEventListener('change', this.handleLieuChange.bind(this));
    },

    async handleVilleChange(event) {
        const villeId = event.target.value;
        
        if (!villeId) {
            this.disableLieuSelect();
            this.clearLieuDetails();
            return;
        }

        // Utilisation du cache
        if (this.cache.lieux.has(villeId)) {
            this.populateLieux(this.cache.lieux.get(villeId));
        } else {
            await this.fetchAndPopulateLieux(villeId);
        }
    },

    async fetchAndPopulateLieux(villeId) {
        try {
            const response = await fetch(`${this.baseUrl}/api/ville/${villeId}/lieux`);
            const lieux = await response.json();
            
            // Mise en cache des résultats
            this.cache.lieux.set(villeId, lieux);
            this.populateLieux(lieux);
        } catch (error) {
            console.error('Erreur:', error);
            this.disableLieuSelect();
        }
    },

    populateLieux(lieux) {
        const options = lieux.map(lieu => 
            new Option(lieu.nom, lieu.id)
        );
        
        this.lieuSelect.innerHTML = '<option value="">Sélectionnez un lieu</option>';
        this.lieuSelect.append(...options);
        this.lieuSelect.disabled = false;
    },

    async handleLieuChange(event) {
        const lieuId = event.target.value;
        if (!lieuId) {
            this.clearLieuDetails();
            return;
        }

        // Utilisation du cache
        if (this.cache.lieuDetails.has(lieuId)) {
            this.updateLieuDetails(this.cache.lieuDetails.get(lieuId));
        } else {
            await this.fetchLieuDetails(lieuId);
        }
    },

    async fetchLieuDetails(lieuId) {
        try {
            const response = await fetch(`${this.baseUrl}/api/lieu-details/${lieuId}`);
            const lieu = await response.json();
            
            // Mise en cache des résultats
            this.cache.lieuDetails.set(lieuId, lieu);
            this.updateLieuDetails(lieu);
        } catch (error) {
            console.error('Erreur:', error);
            this.clearLieuDetails();
        }
    },

    updateLieuDetails(lieu) {
        this.rueInput.value = lieu.rue || '';
        this.codePostalInput.value = lieu.ville.codePostal || '';
        this.latitudeInput.value = lieu.latitude || '';
        this.longitudeInput.value = lieu.longitude || '';
    },

    clearLieuDetails() {
        ['rueInput', 'codePostalInput', 'latitudeInput', 'longitudeInput']
            .forEach(input => this[input].value = '');
    },

    disableLieuSelect() {
        this.lieuSelect.disabled = true;
        this.lieuSelect.innerHTML = '<option value="">Sélectionnez d\'abord une ville</option>';
    },

    async initializeExistingData() {
        const villeId = this.villeSelect.value;
        const lieuId = this.lieuSelect.value;
        
        if (villeId) {
            await this.fetchAndPopulateLieux(villeId);
            if (lieuId) {
                this.lieuSelect.value = lieuId;
                await this.fetchLieuDetails(lieuId);
            }
        }
    }
};
