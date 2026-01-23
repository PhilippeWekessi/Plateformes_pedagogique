document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("formClassement");
    const tableBody = document.querySelector("#tableClassement tbody");
    const message = document.getElementById("message");

    // Exemple : remplir dynamiquement les promotions et années
    const promotions = [{id:1, nom:"Licence 1"}, {id:2, nom:"Licence 2"}];
    const annees = ["2024-2025", "2025-2026"];

    const selectPromo = form.querySelector("select[name='promotion']");
    promotions.forEach(p => selectPromo.innerHTML += `<option value="${p.id}">${p.nom}</option>`);

    const selectAnnee = form.querySelector("select[name='annee']");
    annees.forEach(a => selectAnnee.innerHTML += `<option value="${a}">${a}</option>`);

    form.addEventListener("submit", e => {
        e.preventDefault();
        const promo = selectPromo.value;
        const annee = selectAnnee.value;

        if (!promo || !annee) return;

        fetch(`../backend/lister_classement.php?promotion=${promo}&annee=${annee}`)
            .then(res => res.json())
            .then(data => {
                tableBody.innerHTML = "";
                if (data.length === 0) {
                    message.textContent = "Aucun classement disponible pour cette promotion/année";
                    message.className = "error";
                    return;
                }
                message.textContent = "Classement chargé avec succès !";
                message.className = "success";

                data.forEach((etudiant, index) => {
                    tableBody.innerHTML += `<tr>
                        <td>${index + 1}</td>
                        <td>${etudiant.nom}</td>
                        <td>${etudiant.prenom}</td>
                        <td>${etudiant.note_totale}</td>
                    </tr>`;
                });
            })
            .catch(err => {
                message.textContent = "Erreur lors du chargement du classement.";
                message.className = "error";
                console.error(err);
            });
    });
});
