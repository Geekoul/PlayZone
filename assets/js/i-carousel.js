document.addEventListener("DOMContentLoaded", () => {
	// Récupération des éléments du carousel et des boutons
	const container = document.querySelector(".i-ag-ligne-2 .carousel-track");
	const nextBtn = document.querySelector(".i-ag-ligne-2 .carousel-next");
	const prevBtn = document.querySelector(".i-ag-ligne-2 .carousel-prev");

	const scrollAmount = 270; // Distance de défilement à chaque clic (élément + gap)
	const epsilon = 5;        // Petite marge pour compenser les imprécisions du scroll

	// Remet le scroll du carousel à gauche au chargement de la page
	container.scrollLeft = 0;

	// Clic sur le bouton "suivant"
	nextBtn.addEventListener("click", () => {
		const maxScrollLeft = container.scrollWidth - container.clientWidth; // Scroll max vers la droite

		// Si on est déjà tout à droite (ou presque), retour tout à gauche
		if (container.scrollLeft + epsilon >= maxScrollLeft) {
			container.scrollTo({ left: 0, behavior: "smooth" });
		} else {
			// Sinon, on avance simplement
			container.scrollBy({ left: scrollAmount, behavior: "smooth" });
		}
	});

	// Clic sur le bouton "précédent"
	prevBtn.addEventListener("click", () => {
		// Si on est déjà tout à gauche (ou presque), on saute tout à droite
		if (container.scrollLeft <= epsilon) {
			const maxScrollLeft = container.scrollWidth - container.clientWidth;
			container.scrollTo({ left: maxScrollLeft, behavior: "smooth" });
		} else {
			// Sinon, on recule simplement
			container.scrollBy({ left: -scrollAmount, behavior: "smooth" });
		}
	});
});