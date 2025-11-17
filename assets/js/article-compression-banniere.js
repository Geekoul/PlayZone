document.addEventListener('DOMContentLoaded', ()=> {
  const input = document.getElementById('banniereArticle');
  if (!input) return;

  // paramètres
  const MAX_W    = 720;
  const MAX_H    = 480;
  const QUALITY  = 0.8; // 80%

  // lit le fichier en DataURL, crée une Image, dessine dans un canvas redimensionné
  async function compressToWebP(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onerror = reject;
      reader.onload = () => {
        const img = new Image();
        img.onerror = reject;
        img.onload = () => {
          // calcul du ratio
          const ratio = Math.min(MAX_W / img.width, MAX_H / img.height, 1);
          const w = Math.round(img.width * ratio);
          const h = Math.round(img.height * ratio);

          const canvas = document.createElement('canvas');
          canvas.width  = w;
          canvas.height = h;
          const ctx = canvas.getContext('2d');
          ctx.drawImage(img, 0, 0, w, h);

          canvas.toBlob(blob => {
            if (!blob) return reject(new Error("Canvas toBlob failed"));
            // nommage : on prend le nom d'origine, on change l'extension
            const name = file.name.replace(/\.\w+$/, '.webp');
            resolve(new File([blob], name, { type: 'image/webp' }));
          }, 'image/webp', QUALITY);
        };
        img.src = reader.result;
      };
      reader.readAsDataURL(file);
    });
  }

  input.addEventListener('change', async e => {
    const file = e.target.files[0];
    if (!file) return;

    try {
      const webpFile = await compressToWebP(file);

      // on remplace l'input.files par le nouveau fichier
      const dt = new DataTransfer();
      dt.items.add(webpFile);
      input.files = dt.files;

      // (optionnel) afficher un aperçu
      let prev = document.querySelector('.preview-banner');
      if (!prev) {
        prev = document.createElement('img');
        prev.className = 'preview-banner';
        prev.style.maxWidth = '200px';
        prev.style.display = 'block';
        input.parentNode.insertBefore(prev, input.nextSibling);
      }
      prev.src = URL.createObjectURL(webpFile);

    } catch (err) {
      console.error(err);
      alert("Impossible de traiter l'image pour la bannière.");
    }
  });
});
