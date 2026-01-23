document.addEventListener("DOMContentLoaded", () => {
    chargerEspaces();
    chargerStats();
});

function chargerEspaces() {
    fetch("../backend/lister_espaces.php")
        .then(res => {
            if (!res.ok) {
                throw new Error(`Erreur HTTP! statut: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            const tbody = document.querySelector("#tableEspaces tbody");
            const message = document.getElementById("message");
            tbody.innerHTML = "";

            if (!data || data.length === 0) {
                message.textContent = "Aucun espace pédagogique n’est enregistré. Veuillez en créer un.";
                return;
            }

            message.textContent = "";

            data.forEach(espace => {
                const row = document.createElement("tr");

                const nomCell = document.createElement("td");
                nomCell.textContent = espace.nom_espace || 'N/A';
                row.appendChild(nomCell);

                const promoCell = document.createElement("td");
                promoCell.textContent = espace.promotion || 'Aucune';
                row.appendChild(promoCell);

                const formateurCell = document.createElement("td");
                formateurCell.textContent = espace.formateur || 'Aucun';
                row.appendChild(formateurCell);

                const etudiantsCell = document.createElement("td");
                etudiantsCell.textContent = espace.nombre_etudiants || 0;
                row.appendChild(etudiantsCell);

                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error("Erreur lors du chargement des espaces:", error);
            document.getElementById("message").textContent = "Erreur lors du chargement des espaces pédagogiques.";
        });
}

function chargerStats() {
    fetch("../backend/statistiques_espaces.php")
        .then(res => res.json())
        .then(stats => {
            const ul = document.getElementById("stats");
            ul.innerHTML = `
                <li>Total espaces : ${stats.total_espaces || 0}</li>
                <li>Total étudiants : ${stats.total_etudiants || 0}</li>
                <li>Total formateurs : ${stats.total_formateurs || 0}</li>
            `;
        })
        .catch(error => {
            console.error("Erreur lors du chargement des statistiques:", error);
        });
}
