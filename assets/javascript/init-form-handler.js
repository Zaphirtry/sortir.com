import { FormHandler } from './sortie-form.js';

document.addEventListener('DOMContentLoaded', () => {
    const formSelectors = {
        villeSelect: '#sortie_ville',
        lieuSelect: '#sortie_lieu',
        rueInput: '#lieu-rue',
        codePostalInput: '#lieu-code-postal',
        latitudeInput: '#lieu-latitude',
        longitudeInput: '#lieu-longitude'
    };
    
    FormHandler.init(formSelectors);
});

