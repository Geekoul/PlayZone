document.addEventListener("DOMContentLoaded", () => {
	const scrollAmount = 270;
	const epsilon = 5;

	document.querySelectorAll(".i-npfr-majdp-container").forEach(containerEl => {
		const container = containerEl.querySelector(".carousel-track");
		const nextBtn = containerEl.querySelector(".carousel-next");
		const prevBtn = containerEl.querySelector(".carousel-prev");

		if (!container || !nextBtn || !prevBtn) return;

		container.scrollLeft = 0;

		nextBtn.addEventListener("click", () => {
			const maxScrollLeft = container.scrollWidth - container.clientWidth;
			if (container.scrollLeft + epsilon >= maxScrollLeft) {
				container.scrollTo({ left: 0, behavior: "smooth" });
			} else {
				container.scrollBy({ left: scrollAmount, behavior: "smooth" });
			}
		});

		prevBtn.addEventListener("click", () => {
			if (container.scrollLeft <= epsilon) {
				const maxScrollLeft = container.scrollWidth - container.clientWidth;
				container.scrollTo({ left: maxScrollLeft, behavior: "smooth" });
			} else {
				container.scrollBy({ left: -scrollAmount, behavior: "smooth" });
			}
		});
	});
});
