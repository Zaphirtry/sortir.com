export const FormHandler = {
    init(selectors) {
        console.log('Initialisation avec les sélecteurs:', selectors);
        this.villeSelect = document.querySelector(selectors.villeSelect);
        this.lieuSelect = document.querySelector(selectors.lieuSelect);
        this.rueInput = document.querySelector(selectors.rueInput);
        this.codePostalInput = document.querySelector(selectors.codePostalInput);
        this.latitudeInput = document.querySelector(selectors.latitudeInput);
        this.longitudeInput = document.querySelector(selectors.longitudeInput);

        // Vérification des éléments trouvés
        if (!this.villeSelect) console.error('villeSelect non trouvé');
        if (!this.lieuSelect) console.error('lieuSelect non trouvé');
        if (!this.rueInput) console.error('rueInput non trouvé');
        if (!this.codePostalInput) console.error('codePostalInput non trouvé');
        if (!this.latitudeInput) console.error('latitudeInput non trouvé');
        if (!this.longitudeInput) console.error('longitudeInput non trouvé');

        this.setupEventListeners();
        
        // Si une ville est déjà sélectionnée (cas de l'édition)
        if (this.villeSelect.value) {
            this.initializeExistingData();
        } else {
            this.disableLieuSelect();
        }
    },

    disableLieuSelect() {
        this.lieuSelect.disabled = true;
        this.lieuSelect.innerHTML = '<option value="">Sélectionnez d\'abord une ville</option>';
    },

    setupEventListeners() {
        this.villeSelect.addEventListener('change', (e) => this.handleVilleChange(e));
        this.lieuSelect.addEventListener('change', (e) => this.handleLieuChange(e));
    },

    async handleVilleChange(event) {
        const villeId = event.target.value;
        console.log('Ville sélectionnée:', villeId);
        
        if (!villeId) {
            this.disableLieuSelect();
            this.clearLieuDetails();
            return;
        }
        await this.fetchAndPopulateLieux(villeId);
    },

    clearLieuDetails() {
        this.rueInput.value = '';
        this.codePostalInput.value = '';
        this.latitudeInput.value = '';
        this.longitudeInput.value = '';
    },

    async fetchAndPopulateLieux(villeId) {
        try {
            const baseUrl = window.location.origin; // Obtient l'URL de base du site
            const url = `${baseUrl}/api/ville/${villeId}/lieux`;
            console.log('URL complète appelée:', url);
            
            const response = await fetch(url);
            console.log('Statut de la réponse:', response.status);
            
            const lieux = await response.json();
            console.log('Lieux reçus:', lieux);
            
            this.lieuSelect.innerHTML = '<option value="">Sélectionnez un lieu</option>';
            lieux.forEach(lieu => {
                const option = new Option(lieu.nom, lieu.id);
                this.lieuSelect.add(option);
            });
            this.lieuSelect.disabled = false;
        } catch (error) {
            console.error('Erreur complète:', error);
            this.disableLieuSelect();
        }
    },

    async handleLieuChange(event) {
        const lieuId = event.target.value;
        if (!lieuId) {
            this.clearLieuDetails();
            return;
        }
        await this.fetchLieuDetails(lieuId);
    },

    async fetchLieuDetails(lieuId) {
        try {
            const baseUrl = window.location.origin;
            const url = `${baseUrl}/api/lieu-details/${lieuId}`;
            console.log('URL complète appelée pour les détails:', url);
            
            const response = await fetch(url);
            console.log('Statut de la réponse détails:', response.status);
            
            const lieu = await response.json();
            console.log('Détails du lieu reçus:', lieu);
            
            this.rueInput.value = lieu.rue || '';
            this.codePostalInput.value = lieu.ville.codePostal || '';
            this.latitudeInput.value = lieu.latitude || '';
            this.longitudeInput.value = lieu.longitude || '';
        } catch (error) {
            console.error('Erreur:', error);
            this.clearLieuDetails();
        }
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
