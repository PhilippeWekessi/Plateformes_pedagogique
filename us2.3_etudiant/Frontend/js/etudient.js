// js/etudiant.js - Version CORRIGÉE avec capture des valeurs
class EtudiantManager {
    constructor() {
        this.initializeElements();
        this.initializeEvents();
        this.loadPromotions();
    }

    initializeElements() {
        this.form = document.getElementById('formEtudiant');
        this.submitBtn = document.getElementById('btnSubmit');
        this.messageBox = document.getElementById('message');
        this.promotionSelect = document.getElementById('promotion_id');
        this.successSummary = document.getElementById('success-summary');
        this.promotionInfo = document.getElementById('promotion-info');
        this.selectedPromotionName = document.getElementById('selected-promotion-name');
        this.selectedPromotionYear = document.getElementById('selected-promotion-year');
        
        this.elements = {
            summaryEmail: document.getElementById('summary-email'),
            summaryPassword: document.getElementById('summary-password'),
            summaryPromotion: document.getElementById('summary-promotion')
        };
        
        console.log('✅ Éléments initialisés:', {
            form: !!this.form,
            select: !!this.promotionSelect,
            submitBtn: !!this.submitBtn
        });
    }

    initializeEvents() {
        // Événement de soumission du formulaire
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        } else {
            console.error('❌ Formulaire non trouvé!');
        }
        
        // Événement pour afficher les infos de la promotion sélectionnée
        if (this.promotionSelect) {
            this.promotionSelect.addEventListener('change', () => this.updatePromotionInfo());
        }
        
        // Validation en temps réel
        this.setupRealTimeValidation();
    }

    async loadPromotions() {
        console.log('📡 Début du chargement des promotions...');
        
        if (!this.promotionSelect) {
            console.error('❌ Élément promotionSelect non trouvé!');
            return;
        }
        
        try {
            // Afficher l'état de chargement
            this.promotionSelect.innerHTML = '<option value="">Chargement des promotions...</option>';
            this.promotionSelect.disabled = true;
            
            // URL vers le fichier PHP
            const url = '../backend/get_promotions.php';
            console.log('🔗 URL de requête:', url);
            
            // Faire la requête
            const response = await fetch(url);
            console.log('📥 Réponse reçue, status:', response.status);
            
            // Vérifier si la réponse est OK
            if (!response.ok) {
                throw new Error(`Erreur HTTP ${response.status}: ${response.statusText}`);
            }
            
            // Parser la réponse JSON
            const data = await response.json();
            console.log('📊 Données reçues:', data);
            
            // Vérifier le succès de la requête
            if (data.success && data.promotions && data.promotions.length > 0) {
                console.log(`✅ Chargement de ${data.promotions.length} promotions`);
                this.populatePromotions(data.promotions);
            } else {
                console.error('❌ Aucune promotion disponible ou erreur:', data.message);
                this.showError('promotion', data.message || 'Aucune promotion disponible');
                this.promotionSelect.innerHTML = '<option value="">Aucune promotion disponible</option>';
            }
            
        } catch (error) {
            console.error('❌ Erreur lors du chargement des promotions:', error);
            
            // Afficher un message d'erreur dans le select
            this.promotionSelect.innerHTML = '<option value="">Erreur de chargement</option>';
            
            // Afficher un message d'erreur dans l'interface
            this.showMessage('Impossible de charger les promotions. Vérifiez la connexion au serveur.', 'error');
            
            // Activer le select malgré l'erreur
            this.promotionSelect.disabled = false;
            
        } finally {
            console.log('🏁 Chargement des promotions terminé');
        }
    }

    populatePromotions(promotions) {
        console.log('🔄 Remplissage du select avec', promotions.length, 'promotions');
        
        // Vider le select
        this.promotionSelect.innerHTML = '';
        
        // Ajouter l'option par défaut
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'Sélectionnez une promotion';
        defaultOption.disabled = true;
        defaultOption.selected = true;
        this.promotionSelect.appendChild(defaultOption);
        
        // Ajouter chaque promotion
        promotions.forEach(promo => {
            const option = document.createElement('option');
            option.value = promo.id_promotion;
            
            // Formater le texte affiché
            let displayText = promo.nom_promotion;
            if (promo.annee_academique) {
                displayText += ` - ${promo.annee_academique}`;
            }
            option.textContent = displayText;
            
            // Stocker les données supplémentaires
            option.dataset.nom = promo.nom_promotion;
            option.dataset.annee = promo.annee_academique;
            
            this.promotionSelect.appendChild(option);
        });
        
        // Activer le select
        this.promotionSelect.disabled = false;
        
        console.log('✅ Select rempli avec succès');
    }

    updatePromotionInfo() {
        const selectedOption = this.promotionSelect.options[this.promotionSelect.selectedIndex];
        
        if (this.promotionSelect.value && selectedOption.dataset.nom) {
            // Afficher les informations de la promotion sélectionnée
            this.selectedPromotionName.textContent = selectedOption.dataset.nom;
            this.selectedPromotionYear.textContent = selectedOption.dataset.annee || 'Non spécifiée';
            this.promotionInfo.style.display = 'flex';
            
            console.log('🎯 Promotion sélectionnée:', {
                id: this.promotionSelect.value,
                nom: selectedOption.dataset.nom,
                annee: selectedOption.dataset.annee
            });
        } else {
            // Cacher la boîte d'information
            this.promotionInfo.style.display = 'none';
        }
    }

    async handleSubmit(event) {
        event.preventDefault();
        console.log('📨 Soumission du formulaire');
        
        // Réinitialiser les messages
        this.clearMessages();
        
        // Validation
        if (!this.validateForm()) {
            console.log('❌ Formulaire invalide');
            return;
        }
        
        console.log('✅ Formulaire valide, envoi des données...');
        
        // Désactiver le formulaire pendant l'envoi
        this.setFormState(true);
        
        try {
            // CORRECTION : Récupérer les valeurs DIRECTEMENT depuis les champs
            const data = {
                nom: document.getElementById('nom').value.trim(),
                prenom: document.getElementById('prenom').value.trim(),
                email: document.getElementById('email').value.trim(),
                promotion_id: document.getElementById('promotion_id').value
            };
            
            console.log('=== DONNÉES RÉCUPÉRÉES DIRECTEMENT ===');
            console.log('📝 Nom:', data.nom);
            console.log('👤 Prénom:', data.prenom);
            console.log('📧 Email:', data.email);
            console.log('🎓 Promotion ID:', data.promotion_id);
            console.log('=== FIN DONNÉES ===');
            
            // Vérifier que les données ne sont pas vides (double vérification)
            if (!data.nom || !data.prenom || !data.email || !data.promotion_id) {
                console.error('❌ ERREUR: Données manquantes après récupération!');
                console.error('Données complètes:', data);
                throw new Error('Certains champs sont vides. Veuillez vérifier le formulaire.');
            }
            
            // Envoyer la requête
            const response = await this.sendRequest(data);
            console.log('📤 Réponse reçue, status:', response.status);
            
            // Vérifier si la réponse est OK
            if (!response.ok) {
                const errorText = await response.text();
                console.error('❌ Erreur réponse texte:', errorText);
                
                // Essayer de parser l'erreur comme JSON
                try {
                    const errorJson = JSON.parse(errorText);
                    console.error('❌ Erreur réponse JSON:', errorJson);
                } catch (e) {
                    // Ce n'est pas du JSON, on garde le texte brut
                }
                
                throw new Error(`Erreur HTTP ${response.status}: ${response.statusText}`);
            }
            
            // Parser la réponse JSON
            const result = await response.json();
            console.log('📄 Résultat:', result);
            
            if (result.success) {
                this.handleSuccess(result);
            } else {
                this.handleError(result);
            }
            
        } catch (error) {
            console.error('❌ Erreur lors de la soumission:', error);
            this.handleNetworkError(error);
        } finally {
            this.setFormState(false);
        }
    }

    validateForm() {
        let isValid = true;
        
        // Valider chaque champ
        const fields = ['nom', 'prenom', 'email', 'promotion_id'];
        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        return isValid;
    }

    validateField(fieldName) {
        const field = document.getElementById(fieldName);
        if (!field) {
            console.error(`❌ Champ ${fieldName} non trouvé`);
            return false;
        }
        
        const value = field.value.trim();
        
        switch(fieldName) {
            case 'nom':
            case 'prenom':
                if (value.length < 2) {
                    this.showError(fieldName, 'Doit contenir au moins 2 caractères');
                    return false;
                }
                if (value.length > 100) {
                    this.showError(fieldName, 'Ne peut pas dépasser 100 caractères');
                    return false;
                }
                break;
                
            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    this.showError(fieldName, 'Format d\'email invalide');
                    return false;
                }
                break;
                
            case 'promotion_id':
                if (!value) {
                    this.showError('promotion', 'Veuillez sélectionner une promotion');
                    return false;
                }
                break;
        }
        
        return true;
    }

    setupRealTimeValidation() {
        const fields = this.form.querySelectorAll('.form-input, .form-select');
        
        fields.forEach(field => {
            field.addEventListener('blur', () => {
                this.validateField(field.name);
            });
            
            field.addEventListener('input', () => {
                this.clearFieldError(field.name);
            });
        });
    }

    async sendRequest(data) {
        const url = '../backend/creer_etudiant.php';
        console.log('🚀 Envoi à:', url);
        console.log('📦 Données brutes pour sendRequest:', data);
        
        // Créer manuellement les paramètres URL
        const params = new URLSearchParams();
        
        // Ajouter chaque champ manuellement avec des valeurs par défaut
        params.append('nom', data.nom || '');
        params.append('prenom', data.prenom || '');
        params.append('email', data.email || '');
        params.append('promotion_id', data.promotion_id || '');
        
        console.log('🔤 Paramètres URL créés:', params.toString());
        
        // DEBUG: Vérifier que les paramètres ne sont pas vides
        if (!params.toString() || params.toString() === 'nom=&prenom=&email=&promotion_id=') {
            console.error('❌❌❌ CRITIQUE: Les paramètres URL sont vides!');
            console.error('Données originales reçues:', data);
            console.error('Vérifiez que les IDs des champs sont corrects:');
            console.error('- nom:', document.getElementById('nom'));
            console.error('- prenom:', document.getElementById('prenom'));
            console.error('- email:', document.getElementById('email'));
            console.error('- promotion_id:', document.getElementById('promotion_id'));
            
            throw new Error('Les données du formulaire sont vides. Vérifiez les IDs des champs HTML.');
        }
        
        try {
            console.log('📤 Envoi de la requête fetch...');
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params
            });
            
            console.log('📥 Réponse fetch reçue, status:', response.status);
            return response;
            
        } catch (fetchError) {
            console.error('❌ Erreur lors de fetch:', fetchError);
            throw fetchError;
        }
    }

    handleSuccess(result) {
        console.log('🎉 Succès:', result);
        
        // Afficher le message de succès
        this.showMessage(result.message, 'success');
        
        // Afficher le résumé si des données sont disponibles
        if (result.data) {
            this.showSuccessSummary(result.data);
        }
        
        // Masquer le formulaire
        this.form.style.display = 'none';
        
        // Réinitialiser le formulaire après 5 secondes
        setTimeout(() => {
            this.form.reset();
            this.form.style.display = 'block';
            this.clearMessages();
            this.successSummary.style.display = 'none';
        }, 5000);
    }

    handleError(result) {
        console.error('❌ Erreur du serveur:', result);
        
        // Afficher le message d'erreur général
        this.showMessage(result.message || 'Erreur inconnue', 'error');
        
        // Si l'erreur contient des erreurs par champ
        if (result.errors && typeof result.errors === 'object') {
            Object.keys(result.errors).forEach(field => {
                this.showError(field, result.errors[field]);
            });
        }
        
        // Si l'erreur concerne un champ spécifique
        if (result.field) {
            this.showError(result.field, result.message);
        }
        
        // Afficher les détails dans la console
        console.error('Détails de l\'erreur:', {
            message: result.message,
            errors: result.errors,
            field: result.field,
            fullResponse: result
        });
    }

    handleNetworkError(error) {
        console.error('🌐 Erreur réseau:', error);
        this.showMessage('Erreur de connexion au serveur: ' + error.message, 'error');
    }

    showMessage(message, type) {
        if (!this.messageBox) {
            console.error('❌ Message box non trouvée');
            return;
        }
        
        this.messageBox.textContent = message;
        this.messageBox.className = `message-box ${type}`;
        this.messageBox.style.display = 'block';
        
        // Auto-hide après 5 secondes pour les succès
        if (type === 'success') {
            setTimeout(() => {
                if (this.messageBox) {
                    this.messageBox.style.display = 'none';
                }
            }, 5000);
        }
    }

    showError(field, message) {
        console.log(`⚠️ Affichage erreur pour ${field}:`, message);
        
        const errorEl = document.getElementById(`error-${field}`);
        if (errorEl) {
            errorEl.textContent = message;
        } else {
            console.error(`❌ Élément error-${field} non trouvé`);
        }
        
        const fieldEl = document.getElementById(field) || this.promotionSelect;
        if (fieldEl) {
            fieldEl.classList.add('error');
        }
    }

    clearFieldError(fieldName) {
        const errorEl = document.getElementById(`error-${fieldName}`);
        if (errorEl) {
            errorEl.textContent = '';
        }
        
        const fieldEl = document.getElementById(fieldName) || this.promotionSelect;
        if (fieldEl) {
            fieldEl.classList.remove('error');
        }
    }

    clearMessages() {
        if (this.messageBox) {
            this.messageBox.style.display = 'none';
            this.messageBox.textContent = '';
            this.messageBox.className = 'message-box';
        }
        
        // Clear all field errors
        document.querySelectorAll('.error-message').forEach(el => {
            el.textContent = '';
        });
        
        document.querySelectorAll('.error').forEach(el => {
            el.classList.remove('error');
        });
    }

    showSuccessSummary(data) {
        console.log('📊 Affichage résumé succès:', data);
        
        if (!this.successSummary) {
            console.error('❌ Élément successSummary non trouvé');
            return;
        }
        
        // Remplir les informations
        if (this.elements.summaryEmail) {
            this.elements.summaryEmail.textContent = data.email || '-';
        }
        
        if (this.elements.summaryPassword) {
            this.elements.summaryPassword.textContent = data.password_temp || 'Généré automatiquement';
        }
        
        if (this.elements.summaryPromotion) {
            this.elements.summaryPromotion.textContent = data.promotion?.nom || '-';
        }
        
        // Afficher le résumé
        this.successSummary.style.display = 'block';
    }

    setFormState(isLoading) {
        if (!this.submitBtn) {
            console.error('❌ Bouton submit non trouvé');
            return;
        }
        
        this.submitBtn.disabled = isLoading;
        this.submitBtn.innerHTML = isLoading 
            ? '<i class="fas fa-spinner fa-spin"></i> Création en cours...' 
            : '<i class="fas fa-user-plus"></i> Créer l\'étudiant';
        
        // Désactiver tous les champs pendant le chargement
        const inputs = this.form.querySelectorAll('input, select, button');
        inputs.forEach(input => {
            if (input !== this.submitBtn) {
                input.disabled = isLoading;
            }
        });
        
        console.log('🔄 État du formulaire:', isLoading ? 'chargement' : 'normal');
    }
}

// Gestionnaire d'événements pour le bouton réinitialiser
function setupResetButton() {
    const resetBtn = document.getElementById('btnReset');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (confirm('Voulez-vous réinitialiser le formulaire ? Toutes les données seront perdues.')) {
                const form = document.getElementById('formEtudiant');
                if (form) {
                    form.reset();
                }
                
                // Masquer les infos de promotion
                const promotionInfo = document.getElementById('promotion-info');
                if (promotionInfo) {
                    promotionInfo.style.display = 'none';
                }
                
                // Masquer le résumé
                const successSummary = document.getElementById('success-summary');
                if (successSummary) {
                    successSummary.style.display = 'none';
                }
                
                // Réafficher le formulaire s'il était masqué
                if (form) {
                    form.style.display = 'block';
                }
                
                // Réinitialiser les messages d'erreur
                document.querySelectorAll('.error-message').forEach(el => {
                    el.textContent = '';
                });
                document.querySelectorAll('.error').forEach(el => {
                    el.classList.remove('error');
                });
                
                console.log('🔄 Formulaire réinitialisé');
            }
        });
    }
}

// Initialiser l'application quand le DOM est chargé
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 DOM chargé, initialisation EtudiantManager...');
    
    // Configurer le bouton réinitialiser
    setupResetButton();
    
    // Initialiser le gestionnaire d'étudiants
    const etudiantManager = new EtudiantManager();
    
    // Exposer l'instance globalement pour le débogage
    window.etudiantManager = etudiantManager;
    
    console.log('✅ EtudiantManager initialisé');
    
    // Fonction pour vérifier rapidement les IDs des champs
    window.checkFormFields = function() {
        console.log('🔍 Vérification des champs du formulaire:');
        const fields = ['nom', 'prenom', 'email', 'promotion_id'];
        fields.forEach(id => {
            const el = document.getElementById(id);
            console.log(`${id}:`, el ? `✅ Trouvé (valeur: "${el.value}")` : '❌ NON TROUVÉ');
        });
    };
    
    // Exécuter la vérification après un délai
    setTimeout(window.checkFormFields, 1000);
});

// Fonction pour tester rapidement depuis la console
window.testFormSubmission = async function(testData = null) {
    console.log('🧪 TEST MANUEL DE SOUMISSION');
    
    const data = testData || {
        nom: 'Acakpo',
        prenom: 'Thibaut',
        email: 'test_' + Date.now() + '@test.com',
        promotion_id: '1'
    };
    
    console.log('📦 Données de test:', data);
    
    const params = new URLSearchParams();
    params.append('nom', data.nom);
    params.append('prenom', data.prenom);
    params.append('email', data.email);
    params.append('promotion_id', data.promotion_id);
    
    console.log('🔤 Paramètres:', params.toString());
    
    try {
        const response = await fetch('../backend/creer_etudiant.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: params
        });
        
        console.log('📥 Réponse status:', response.status);
        const result = await response.json();
        console.log('📄 Résultat:', result);
        
        // Afficher dans l'interface aussi
        const messageBox = document.getElementById('message');
        if (messageBox && result.message) {
            messageBox.textContent = result.message;
            messageBox.className = result.success ? 'message-box success' : 'message-box error';
            messageBox.style.display = 'block';
        }
        
        return result;
        
    } catch (error) {
        console.error('❌ Erreur:', error);
        return null;
    }
};

// Fonction pour afficher les valeurs actuelles du formulaire
window.showCurrentFormValues = function() {
    const values = {
        nom: document.getElementById('nom')?.value || 'VIDE',
        prenom: document.getElementById('prenom')?.value || 'VIDE',
        email: document.getElementById('email')?.value || 'VIDE',
        promotion_id: document.getElementById('promotion_id')?.value || 'VIDE'
    };
    console.log('📋 Valeurs actuelles du formulaire:', values);
    return values;
};

// Ajouter un bouton de debug dans la page
setTimeout(() => {
    const debugBtn = document.createElement('button');
    debugBtn.textContent = '🐛 Debug';
    debugBtn.style.position = 'fixed';
    debugBtn.style.bottom = '60px';
    debugBtn.style.right = '10px';
    debugBtn.style.zIndex = '1000';
    debugBtn.style.padding = '8px 12px';
    debugBtn.style.background = '#f0ad4e';
    debugBtn.style.color = 'white';
    debugBtn.style.border = 'none';
    debugBtn.style.borderRadius = '4px';
    debugBtn.style.cursor = 'pointer';
    debugBtn.style.fontSize = '12px';
    debugBtn.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';
    
    debugBtn.addEventListener('click', () => {
        console.log('=== 🐛 DEBUG RAPIDE ===');
        showCurrentFormValues();
        console.log('=== FIN DEBUG ===');
    });
    
    document.body.appendChild(debugBtn);
}, 2000);

console.log('✅ Script etudiant.js chargé avec succès');