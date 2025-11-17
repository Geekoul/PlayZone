document.addEventListener("DOMContentLoaded", () => {
	const burger = document.querySelector(".menu-burger");
	const nav = document.querySelector("#en-tete nav");

	burger.addEventListener("click", () => {
		burger.classList.toggle("open");
		nav.classList.toggle("open");
	});
});