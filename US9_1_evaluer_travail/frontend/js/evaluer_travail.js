document.addEventListener("DOMContentLoaded", chargerTravauxLivres);

function chargerTravauxLivres() {
    const messageElement = document.getElementById("message");
    messageElement.textContent = "Chargement des travaux en cours...";

    fetch("../backend/lister_travaux_livres.php")
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const select = document.getElementById("livraison");
            select.innerHTML = "<option value=''>-- Choisir un travail livré --</option>";

            if (data && data.length > 0) {
                data.forEach(travail => {
                    const option = document.createElement("option");
                    option.value = travail.id_livraison;
                    option.textContent = `${travail.titre} - ${travail.etudiant} (Livré le: ${new Date(travail.date_livraison).toLocaleString()})`;
                    select.appendChild(option);
                });
                messageElement.textContent = "";
            } else {
                messageElement.textContent = "Aucun travail livré à évaluer";
                messageElement.style.color = "orange";
            }
        })
        .catch(error => {
            console.error("Erreur complète:", error);
            messageElement.textContent = "Erreur de chargement des travaux livrés: " + error.message;
            messageElement.style.color = "red";
        });
}

document.getElementById("formEvaluation").addEventListener("submit", function(e) {
    e.preventDefault();

    const livraison = document.getElementById("livraison").value;
    const note = document.getElementById("note").value;
    const commentaire = document.getElementById("commentaire").value;
    const messageElement = document.getElementById("message");

    if (!livraison) {
        messageElement.textContent = "Veuillez sélectionner un travail";
        messageElement.style.color = "red";
        return;
    }

    if (!note || isNaN(note) || note < 0 || note > 20) {
        messageElement.textContent = "Veuillez entrer une note valide (0-20)";
        messageElement.style.color = "red";
        return;
    }

    if (!commentaire) {
        messageElement.textContent = "Veuillez entrer un commentaire";
        messageElement.style.color = "red";
        return;
    }

    messageElement.textContent = "Envoi de l'évaluation en cours...";
    messageElement.style.color = "black";

    const data = {
        livraison: livraison,
        note: note,
        commentaire: commentaire
    };

    fetch("../backend/evaluer_travail.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`Erreur serveur: ${text}`);
            });
        }
        return response.json();
    })
    .then(result => {
        messageElement.style.color = result.success ? "green" : "red";
        messageElement.textContent = result.message;

        if (result.success) {
            document.getElementById("formEvaluation").reset();
            chargerTravauxLivres(); // Recharger la liste des travaux
        }
    })
    .catch(error => {
        console.error("Erreur:", error);
        messageElement.textContent = "Erreur lors de l'envoi: " + error.message;
        messageElement.style.color = "red";
    });
});
