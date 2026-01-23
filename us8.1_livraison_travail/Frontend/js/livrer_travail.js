document.addEventListener("DOMContentLoaded", function() {
    // Charger les travaux disponibles
    chargerTravaux();

    function chargerTravaux() {
        // Utilise un chemin relatif correct
        fetch("../Backend/lister_travaux_etudiant.php?etudiant_id=7")
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`Erreur HTTP! statut: ${response.status}. Réponse: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                const select = document.getElementById("id_travail");
                select.innerHTML = "<option value=''>-- Sélectionner un travail --</option>";

                if (data.success && data.travaux && data.travaux.length > 0) {
                    data.travaux.forEach(travail => {
                        const option = document.createElement("option");
                        option.value = travail.id_travail;
                        const type = travail.type ? travail.type : "Non spécifié";
                        const dateFin = travail.date_fin ? new Date(travail.date_fin).toLocaleDateString() : "Non spécifiée";
                        option.textContent = `${travail.titre} (${type}) - À rendre le ${dateFin}`;
                        select.appendChild(option);
                    });
                } else {
                    const message = document.getElementById("message");
                    message.textContent = data.message || "Aucun travail assigné trouvé";
                    message.className = "error";
                }
            })
            .catch(error => {
                console.error("Erreur complète:", error);
                const message = document.getElementById("message");
                message.textContent = "Erreur: " + error.message;
                message.className = "error";
            });
    }

    // Gestion de la soumission du formulaire
    document.getElementById("formLivraison").addEventListener("submit", function(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        const message = document.getElementById("message");
        message.textContent = "Envoi en cours...";
        message.className = "";

        fetch("../Backend/livrer_travail.php", {
            method: "POST",
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`Erreur serveur: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                message.textContent = data.message;
                message.className = "success";
                form.reset();
                chargerTravaux();
            } else {
                message.textContent = data.message || "Erreur lors de la livraison";
                message.className = "error";
            }
        })
        .catch(error => {
            console.error("Erreur complète:", error);
            message.textContent = error.message || "Erreur réseau";
            message.className = "error";
        });
    });
});
