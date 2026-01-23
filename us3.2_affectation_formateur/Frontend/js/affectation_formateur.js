document.addEventListener('DOMContentLoaded', function() {
    // Chemins relatifs
    const BASE_URL = '../backend/';
    const URL_FORMATEURS = BASE_URL + 'lister_formateurs.php';
    const URL_ESPACES = BASE_URL + 'lister_espaces.php';
    const URL_AFFECTER = BASE_URL + 'affectation_formateur.php';

    // Charger les formateurs
    async function chargerFormateurs() {
        const selectElement = document.querySelector("select[name='formateur_id']");

        try {
            const response = await fetch(URL_FORMATEURS);
            if (!response.ok) {
                throw new Error(`Erreur HTTP ${response.status}`);
            }

            const data = await response.json();
            if (data.success && data.formateurs && data.formateurs.length > 0) {
                data.formateurs.forEach(formateur => {
                    const option = document.createElement("option");
                    option.value = formateur.id_user;
                    option.textContent = `${formateur.nom} ${formateur.prenom}`;
                    selectElement.appendChild(option);
                });
            } else {
                console.log('Aucun formateur disponible');
            }
        } catch (error) {
            console.error('Erreur chargement formateurs:', error);
        }
    }

    // Charger les espaces pédagogiques
    async function chargerEspaces() {
        const selectElement = document.querySelector("select[name='espace_id']");

        try {
            const response = await fetch(URL_ESPACES);
            if (!response.ok) {
                throw new Error(`Erreur HTTP ${response.status}`);
            }

            const data = await response.json();
            if (data.success && data.espaces && data.espaces.length > 0) {
                data.espaces.forEach(espace => {
                    const option = document.createElement("option");
                    option.value = espace.id_espace;
                    option.textContent = `${espace.nom} (${espace.matiere})`;
                    selectElement.appendChild(option);
                });
            } else {
                console.log('Aucun espace pédagogique disponible');
            }
        } catch (error) {
            console.error('Erreur chargement espaces:', error);
        }
    }

    // Charger les formateurs et les espaces au démarrage
    chargerFormateurs();
    chargerEspaces();

    // Gestionnaire de soumission du formulaire
    document.getElementById("formAffectation").addEventListener("submit", async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const messageElement = document.getElementById("message");

        try {
            const response = await fetch(URL_AFFECTER, {
                method: "POST",
                body: formData
            });

            const data = await response.json();

            if (response.ok) {
                messageElement.textContent = data.message;
                messageElement.className = data.success ? "success" : "error";
            } else {
                throw new Error(data.message || "Erreur lors de l'affectation");
            }
        } catch (error) {
            console.error('Erreur lors de la soumission:', error);
            messageElement.textContent = `❌ ${error.message}`;
            messageElement.className = "error";
        }
    });
});
