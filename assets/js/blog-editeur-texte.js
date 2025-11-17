document.addEventListener('DOMContentLoaded', () => {
  // Sélection des éléments du DOM
  const boldBtn      = document.getElementById('boldBtn');
  const italicBtn    = document.getElementById('italicBtn');
  const underlineBtn = document.getElementById('underlineBtn');
  const strikeBtn    = document.getElementById('strikeBtn');
  const linkBtn      = document.getElementById('linkBtn');
  const unlinkBtn    = document.getElementById('unlinkBtn');
  const imageBtn     = document.getElementById('imageBtn');
  const imageInput   = document.getElementById('imageInput');
  const editor       = document.getElementById('editor');
  const hiddenInput  = document.getElementById('contenuBlog');
  const form         = editor?.closest('form');

  // Paramètres
  const MAX_IMAGES = 30;
  const MAX_W      = 720;
  const MAX_H      = 480;
  const QUALITY    = 0.6;

  if (!editor || !hiddenInput) return;

  // Fonction pour compter les <img> déjà insérées
  function countImages() {
    return editor.querySelectorAll('img').length;
  }

  // Insère un fragment HTML à la position du curseur
  function insertHTMLAtCursor(html) {
    const sel = window.getSelection();
    if (!sel.rangeCount) {
      editor.insertAdjacentHTML('beforeend', html);
      return;
    }
    const range = sel.getRangeAt(0);
    if (!editor.contains(range.commonAncestorContainer)) {
      editor.insertAdjacentHTML('beforeend', html);
      return;
    }
    range.deleteContents();
    const container = document.createElement('div');
    container.innerHTML = html;
    const frag = document.createDocumentFragment();
    while (container.firstChild) {
      frag.appendChild(container.firstChild);
    }
    range.insertNode(frag);
    range.collapse(false);
    sel.removeAllRanges();
    sel.addRange(range);
  }

  // Compression client-side en WebP
  function compressImage(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onerror = reject;
      reader.onload = () => {
        const img = new Image();
        img.onerror = reject;
        img.onload = () => {
          const ratio = Math.min(MAX_W / img.width, MAX_H / img.height, 1);
          const w = Math.floor(img.width * ratio);
          const h = Math.floor(img.height * ratio);
          const canvas = document.createElement('canvas');
          canvas.width = w;
          canvas.height = h;
          canvas.getContext('2d').drawImage(img, 0, 0, w, h);
          canvas.toBlob(blob => {
            const reader2 = new FileReader();
            reader2.onerror = reject;
            reader2.onload = () => resolve(reader2.result);
            reader2.readAsDataURL(blob);
          }, 'image/webp', QUALITY);
        };
        img.src = reader.result;
      };
      reader.readAsDataURL(file);
    });
  }

  // Convertit les liens YouTube en <iframe>
  editor.addEventListener('input', convertirLienYoutubeDansEditor);
  function convertirLienYoutubeDansEditor() {
    const regexGlobal = /https?:\/\/(?:www\.)?(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})([^\s<]*)/g;

    const elements = Array.from(editor.childNodes);

    elements.forEach(node => {
      if (node.nodeType === Node.TEXT_NODE || node.nodeType === Node.ELEMENT_NODE) {
        let html = node.innerHTML || node.textContent;
        let changed = false;

        html = html.replace(regexGlobal, (full, videoId, rawParams) => {
          // Nettoyage des éventuels caractères ? ou & au début
          const cleanedParams = rawParams.replace(/^[?&]/, '');
          const src = `https://www.youtube.com/embed/${videoId}` + (cleanedParams ? `?${cleanedParams}` : '');

          changed = true;
          return `
  <iframe width="560" height="315" src="${src}"
    title="YouTube video player" frameborder="0"
    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
    referrerpolicy="strict-origin-when-cross-origin" allowfullscreen>
  </iframe>`;
        });

        if (changed) {
          const container = document.createElement('div');
          container.innerHTML = html;
          editor.replaceChild(container, node);
        }
      }
    });
  }

  // Boutons de formatage
  boldBtn     && boldBtn.addEventListener('click',    () => { document.execCommand('bold');       editor.focus(); });
  italicBtn   && italicBtn.addEventListener('click',  () => { document.execCommand('italic');     editor.focus(); });
  underlineBtn&& underlineBtn.addEventListener('click',()=> { document.execCommand('underline');  editor.focus(); });
  strikeBtn   && strikeBtn.addEventListener('click',  () => { document.execCommand('strikeThrough'); editor.focus(); });
  linkBtn     && linkBtn.addEventListener('click',    () => {
    editor.focus();
    const url = prompt('Entrez l’URL (http://…) :', 'https://');
    if (url) document.execCommand('createLink', false, url);
  });
  unlinkBtn   && unlinkBtn.addEventListener('click',  () => { document.execCommand('unlink');      editor.focus(); });

  // Insertion d’image
  imageBtn && imageBtn.addEventListener('click', () => {
    if (countImages() >= MAX_IMAGES) {
      alert(`Vous avez atteint la limite de ${MAX_IMAGES} images.`);
    } else {
      imageInput.click();
    }
  });

  imageInput && imageInput.addEventListener('change', async e => {
    const file = e.target.files[0];
    if (!file) return;
    if (countImages() >= MAX_IMAGES) {
      alert(`Vous avez atteint la limite de ${MAX_IMAGES} images.`);
      e.target.value = '';
      return;
    }
    try {
      const dataURL = await compressImage(file);
      insertHTMLAtCursor(`<img src="${dataURL}" alt="${file.name}">`);
    } catch (err) {
      console.error(err);
      alert('Erreur lors de la lecture de l’image.');
    } finally {
      e.target.value = '';
    }
  });

  // Avant envoi du formulaire, on copie le HTML dans le champ caché
  form.addEventListener('submit', () => {
    let contenu = editor.innerHTML.trim();
    hiddenInput.value = editor.innerHTML.trim();
    console.log('>> contenu envoyé :', hiddenInput.value);
  });
});
