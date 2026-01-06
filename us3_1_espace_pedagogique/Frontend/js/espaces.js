document.getElementById("formEspace").addEventListener("submit", async e => {
    e.preventDefault();

    const formData = new FormData(e.target);

    const response = await fetch("../backend/creer_espace.php", {
        method: "POST",
        body: formData
    });

    const data = await response.json();
    document.getElementById("message").textContent = data.message;

    if (data.success) {
        chargerEspaces();
        e.target.reset();
    }
});

async function chargerEspaces() {
    const res = await fetch("../backend/lister_espaces.php");
    const espaces = await res.json();

    const ul = document.getElementById("listeEspaces");
    ul.innerHTML = "";

    espaces.forEach(e => {
        const li = document.createElement("li");
        li.textContent = `${e.nom} - ${e.matiere}`;
        ul.appendChild(li);
    });
}

chargerEspaces();
