document.addEventListener("DOMContentLoaded", function() {
    chargerEtudiants();

    function chargerEtudiants() {
        fetch("../backend/lister_etudiants.php")
            .then(res => {
                if (!res.ok) {
                    throw new Error(`Erreur HTTP! statut: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                const select = document.getElementById("etudiant");
                select.innerHTML = "<option value=''>-- Choisir un étudiant --</option>";

                if (data && data.length > 0) {
                    data.forEach(e => {
                        const option = document.createElement("option");
                        option.value = e.id_etudiant;
                        option.textContent = `${e.nom} ${e.prenom}`;
                        select.appendChild(option);
                    });
                } else {
                    console.log("Aucun étudiant trouvé");
                }
            })
            .catch(error => {
                console.error("Erreur lors du chargement des étudiants:", error);
            });
    }

    document.getElementById("formTravail").addEventListener("submit", function(e) {
        e.preventDefault();

        const etudiant = document.getElementById("etudiant").value;
        const titre = document.getElementById("titre").value;
        const type = document.getElementById("type").value;
        const consignes = document.getElementById("consignes").value;
        const date_limite = document.getElementById("date_limite").value;

        // Vérification des champs obligatoires
        if (!etudiant || !titre || !type || !consignes || !date_limite) {
            document.getElementById("message").textContent = "Veuillez remplir tous les champs obligatoires.";
            document.getElementById("message").style.color = "red";
            return;
        }

        const data = {
            id_etudiant: etudiant,
            titre: titre,
            type: type,
            consignes: consignes,
            date_limite: date_limite
        };

        fetch("../backend/assigner_travail.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        })
        .then(res => {
            if (!res.ok) {
                throw new Error(`Erreur HTTP! statut: ${res.status}`);
            }
            return res.json();
        })
        .then(result => {
            const msg = document.getElementById("message");
            msg.style.color = result.success ? "green" : "red";
            msg.textContent = result.message;
            if (result.success) {
                this.reset();
            }
        })
        .catch(error => {
            console.error("Erreur lors de l'assignation du travail:", error);
            document.getElementById("message").textContent = "Erreur lors de l'assignation du travail.";
            document.getElementById("message").style.color = "red";
        });
    });
});
