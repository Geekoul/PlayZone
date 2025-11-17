<script src="/assets/js/blog-editeur-texte.js" defer></script>

	<label for="editor">Contenu du blog :</label>
		<div class="editor-container">
			<div class="toolbar">
				<button type="button" id="boldBtn" title="Gras"><b>B</b></button>
				<button type="button" id="italicBtn" title="Italic"><i>i</i></button>
				<button type="button" id="underlineBtn" title="Souligné"><u>U</u></button>
				<button type="button" id="strikeBtn" title="Barré"><s>S</s></button>
				<button type="button" id="linkBtn" title="Insérer un lien">🔗</button>
				<button type="button" id="unlinkBtn" title="Supprimer le lien">⛔</button>
				<button type="button" id="imageBtn" title="Insérer une image">🖼️</button>
				<input type="file" id="imageInput" accept="image/png, image/jpeg, image/webp" style="display: none">
			</div>
			<div id="editor" contenteditable="true">
				<?= isset($Blog) ? $blog['Blog_contenu'] : '<p>Commencez à écrire…</p>' ?>
			</div>
		</div>