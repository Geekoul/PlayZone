document.addEventListener("DOMContentLoaded", () => {
  const logoInput     = document.getElementById("utilisateur_logo");

  if (logoInput) {
    logoInput.addEventListener("change", () => {
      handleImageCompression(logoInput, 600, 600);
    });
  }

  async function handleImageCompression(inputElement, maxWidth, maxHeight) {
    const file = inputElement.files[0];
    if (!file) return;

    const img = new Image();
    img.src = URL.createObjectURL(file);

    img.onload = async () => {
      const canvas = document.createElement("canvas");
      const ratio = Math.min(maxWidth / img.width, maxHeight / img.height, 1);
      const width = img.width * ratio;
      const height = img.height * ratio;
      canvas.width = width;
      canvas.height = height;

      const ctx = canvas.getContext("2d");
      ctx.drawImage(img, 0, 0, width, height);

      canvas.toBlob(
        (blob) => {
          if (!blob) return;
          const newFile = new File([blob], file.name.replace(/\.[^.]+$/, '.webp'), {
            type: "image/webp",
            lastModified: Date.now(),
          });

          // Crée un nouveau DataTransfer pour remplacer l'image dans le champ
          const dataTransfer = new DataTransfer();
          dataTransfer.items.add(newFile);
          inputElement.files = dataTransfer.files;
        },
        "image/webp",
        0.6
      );
    };
  }
});
