// espaces.js
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Page création espace pédagogique chargée');

    // Chemins relatifs depuis Frontend/
    const BASE_URL = '../backend/';
    const URL_CREER = BASE_URL + 'creer_espace.php';
    const URL_LISTER = BASE_URL + 'lister_espaces.php';
    const URL_PROMOTIONS = BASE_URL + 'lister_promotions.php';

    console.log('🔗 URLs API:');
    console.log('- Création:', URL_CREER);
    console.log('- Liste:', URL_LISTER);
    console.log('- Promotions:', URL_PROMOTIONS);

    async function testAPIConnection() {
        console.log('🧪 Test de connexion aux APIs...');

        try {
            const response = await fetch(URL_LISTER);
            console.log('📡 Test lister_espaces.php - Status:', response.status);

            if (response.ok) {
                const data = await response.json();
                console.log('✅ API liste fonctionnelle:', data.success ? 'OUI' : 'NON');
            } else {
                console.error('❌ API liste inaccessible');
            }
        } catch (error) {
            console.error('❌ Erreur test connexion:', error);
        }
    }

    async function chargerEspaces() {
        const listeElement = document.getElementById("listeEspaces");

        try {
            console.log('📡 Chargement des espaces...');

            const response = await fetch(URL_LISTER);

            if (!response.ok) {
                throw new Error(`Erreur HTTP ${response.status}`);
            }

            const data = await response.json();
            console.log('📊 Données reçues:', data);

            if (data.success && data.espaces && data.espaces.length > 0) {
                listeElement.innerHTML = "";

                data.espaces.forEach(espace => {
                    const li = document.createElement("li");

                    let text = `📚 ${espace.nom} - ${espace.matiere}`;
                    if (espace.annee_academique) {
                        text += ` (${espace.annee_academique})`;
                    }

                    li.textContent = text;
                    listeElement.appendChild(li);
                });

                console.log(`✅ ${data.espaces.length} espaces chargés`);
            } else {
                listeElement.innerHTML = "<li>📭 Aucun espace pédagogique créé pour le moment</li>";
            }

        } catch (error) {
            console.error('❌ Erreur chargement espaces:', error);
            listeElement.innerHTML = `<li>❌ Erreur: ${error.message}</li>`;
        }
    }

    async function chargerPromotions() {
        const selectElement = document.getElementById("promotion");

        try {
            console.log('📡 Chargement des promotions...');

            const response = await fetch(URL_PROMOTIONS);

            if (!response.ok) {
                throw new Error(`Erreur HTTP ${response.status}`);
            }

            const data = await response.json();
            console.log('📊 Promotions reçues:', data);

            if (data.success && data.promotions && data.promotions.length > 0) {
                data.promotions.forEach(promotion => {
                    const option = document.createElement("option");
                    option.value = promotion.id_promotion;
                    option.textContent = promotion.nom_promotion;
                    selectElement.appendChild(option);
                });

                console.log(`✅ ${data.promotions.length} promotions chargées`);
            } else {
                console.log('📭 Aucune promotion disponible');
            }

        } catch (error) {
            console.error('❌ Erreur chargement promotions:', error);
        }
    }

    // Tester la connexion aux APIs
    testAPIConnection();

    // Charger les espaces existants
    chargerEspaces();

    // Charger les promotions
    chargerPromotions();

    // Gestionnaire de soumission du formulaire
    document.getElementById("formEspace").addEventListener("submit", async function(e) {
        e.preventDefault();

        console.log('📨 Début de la soumission du formulaire');

        const messageElement = document.getElementById("message");
        const submitButton = this.querySelector("button[type='submit']");

        // Réinitialiser
        messageElement.textContent = "";
        messageElement.className = "";

        // Désactiver le bouton
        submitButton.disabled = true;
        const originalText = submitButton.textContent;
        submitButton.textContent = "Création en cours...";

        try {
            // 1. Récupérer les données du formulaire
            const nom = document.getElementById('nom').value.trim();
            const matiere = document.getElementById('matiere').value.trim();
            const id_promotion = document.getElementById('promotion').value;

            console.log('📝 Données saisies:', { nom, matiere, id_promotion });

            // 2. Validation simple
            if (!nom || !matiere || !id_promotion) {
                throw new Error("Veuillez remplir tous les champs obligatoires");
            }

            // 3. Préparer les données pour l'envoi
            const formData = new FormData();
            formData.append('nom', nom);
            formData.append('matiere', matiere);
            formData.append('id_promotion', id_promotion);

            console.log('📤 Envoi à:', URL_CREER);

            // 4. Envoyer la requête POST
            const response = await fetch(URL_CREER, {
                method: 'POST',
                body: formData
            });

            console.log('📥 Réponse reçue - Status:', response.status);

            // 5. Vérifier la réponse
            if (!response.ok) {
                const errorText = await response.text();
                console.error('❌ Erreur réponse:', errorText);

                // Essayer de parser l'erreur
                try {
                    const errorJson = JSON.parse(errorText);
                    throw new Error(errorJson.message || `Erreur HTTP ${response.status}`);
                } catch (parseError) {
                    throw new Error(`Erreur ${response.status}: ${response.statusText}`);
                }
            }

            // 6. Parser la réponse JSON
            const result = await response.json();
            console.log('✅ Résultat:', result);

            // 7. Afficher le message à l'utilisateur
            messageElement.textContent = result.message;
            messageElement.className = result.success ? "success" : "error";

            if (result.success) {
                // Réinitialiser le formulaire
                this.reset();

                // Recharger la liste des espaces
                chargerEspaces();
            }

        } catch (error) {
            console.error('❌ Erreur lors de la soumission:', error);
            messageElement.textContent = "❌ " + error.message;
            messageElement.className = "error";
        } finally {
            // Réactiver le bouton
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    });
});
