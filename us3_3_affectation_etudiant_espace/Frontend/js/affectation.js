document.addEventListener("DOMContentLoaded", () => {
    // Déclaration des éléments DOM
    const form = document.getElementById("formAffectation");
    const message = document.getElementById("message");
    const promotionSelect = document.getElementById("promotion");
    const etudiantSelect = document.getElementById("etudiant");
    const espaceSelect = document.getElementById("espace");

    // Charger les promotions
    async function chargerPromotions() {
        try {
            const response = await fetch("../backend/lister_promotions.php");
            const data = await response.json();
            console.log("Données des promotions:", data); // Log détaillé

            if (data.success && data.promotions && data.promotions.length > 0) {
                data.promotions.forEach(promotion => {
                    const option = document.createElement("option");
                    option.value = promotion.id_promotion;
                    option.textContent = `${promotion.nom_promotion} (${promotion.annee_academique})`;
                    promotionSelect.appendChild(option);
                });
            } else {
                console.log("Aucune promotion disponible ou données incorrectes");
            }
        } catch (error) {
            console.error("Erreur lors du chargement des promotions:", error);
        }
    }

    // Charger les étudiants en fonction de la promotion sélectionnée
    promotionSelect.addEventListener("change", async () => {
        const promotionId = promotionSelect.value;
        etudiantSelect.innerHTML = '<option value="">-- Sélectionner --</option>';

        if (!promotionId) return;

        try {
            const response = await fetch(`../backend/lister_etudiants.php?promotion_id=${promotionId}`);
            const data = await response.json();
            console.log("Données des étudiants:", data); // Log détaillé

            if (data.success && data.etudiants && data.etudiants.length > 0) {
                data.etudiants.forEach(etudiant => {
                    const option = document.createElement("option");
                    option.value = etudiant.id_user;
                    option.textContent = `${etudiant.nom} ${etudiant.prenom}`;
                    etudiantSelect.appendChild(option);
                });
            } else {
                console.log("Aucun étudiant disponible ou données incorrectes");
            }
        } catch (error) {
            console.error("Erreur lors du chargement des étudiants:", error);
        }
    });

    // Charger les espaces pédagogiques
    async function chargerEspaces() {
        try {
            const response = await fetch("../backend/lister_espaces.php");
            const data = await response.json();
            console.log("Données des espaces:", data); // Log détaillé

            if (data.success && data.espaces && data.espaces.length > 0) {
                data.espaces.forEach(espace => {
                    const option = document.createElement("option");
                    option.value = espace.id_espace;
                    option.textContent = `${espace.nom_espace} (${espace.matiere})`;
                    espaceSelect.appendChild(option);
                });
            } else {
                console.log("Aucun espace pédagogique disponible ou données incorrectes");
            }
        } catch (error) {
            console.error("Erreur lors du chargement des espaces:", error);
        }
    }

    // Gestionnaire de soumission du formulaire
    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const etudiant_id = etudiantSelect.value;
        const espace_id = espaceSelect.value;

        if (!etudiant_id || !espace_id) {
            message.innerText = "Veuillez sélectionner un étudiant et un espace pédagogique.";
            message.style.color = "var(--error)";
            return;
        }

        try {
            const response = await fetch("../backend/affecter_etudiant.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    etudiant_id: etudiant_id,
                    espace_id: espace_id
                })
            });

            const data = await response.json();
            message.innerText = data.message;
            message.style.color = data.success ? "var(--success)" : "var(--error)";
        } catch (error) {
            message.innerText = "Erreur serveur";
            message.style.color = "var(--error)";
        }
    });

    // Charger les données au démarrage
    chargerPromotions();
    chargerEspaces();
});
