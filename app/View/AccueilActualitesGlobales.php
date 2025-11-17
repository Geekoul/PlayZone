<?php
use App\Helpers\DateHelper;

// 1) Récupère les 10 dernières actus
$rawArticles = $articleModel->getLastArticlesPaged(10, 0);

// 2) Prépare les 10 “slots” avec des valeurs par défaut
$defaults = [
    1 => ['img'=>'/assets/images/Banniere_default.webp','href'=>'/articleview','title'=>"Titre Actualité 1",'date'=>"Il y a 5 Heures"],
    2 => ['img'=>'/assets/images/Banniere_default.webp','href'=>'/articleview','title'=>"Titre Actualité 2",'date'=>"Hier à 09:30"],
    3 => ['img'=>'/assets/images/Banniere_default.webp','href'=>'/articleview','title'=>"Titre Actualité 3",'date'=>"1 janv., 12:00"],
    4 => ['img'=>'/assets/images/Banniere_default.webp','href'=>'/articleview','title'=>"Titre Actualité 4",'date'=>"1 janv., 12:00"],
    5 => ['img'=>'/assets/images/Banniere_default.webp','href'=>'/articleview','title'=>"Titre Actualité 5",'date'=>"1 janv., 12:00"],
    6 => ['img'=>'/assets/images/Banniere_default.webp','href'=>'/articleview','title'=>"Titre Actualité 6",'date'=>"1 janv., 12:00"],
    7 => ['img'=>'/assets/images/Banniere_default.webp','href'=>'/articleview','title'=>"Titre Actualité 7",'date'=>"1 janv., 12:00"],
    8 => ['img'=>'/assets/images/Banniere_default.webp','href'=>'/articleview','title'=>"Titre Actualité 8",'date'=>"1 janv., 12:00"],
    9 => ['img'=>'/assets/images/Banniere_default.webp','href'=>'/articleview','title'=>"Titre Actualité 9",'date'=>"1 janv., 12:00"],
    10=> ['img'=>'/assets/images/Banniere_default.webp','href'=>'/articleview','title'=>"Titre Actualité 10",'date'=>"1 janv., 12:00"],
];

$slots = $defaults;

// 3) Mappe les articles aux slots (1 → le plus récent, …)
$idx = 1;
foreach ($rawArticles as $a) {
    if ($idx > 10) break;

    $slug  = (string)($a['slug'] ?? '');
    $href  = $slug !== '' ? '/article/' . rawurlencode($slug) : '/articleview';

    $img   = trim((string)($a['banniere'] ?? ''));
    if ($img === '' || $img === '0') $img = '/assets/images/Banniere_default.webp';
    if ($img[0] !== '/') $img = '/'.$img;

    $title = trim((string)($a['titre'] ?? 'Sans titre'));
    $date  = DateHelper::relatif((string)($a['published_at'] ?? ''));

    $slots[$idx] = [
        'img'   => $img,
        'href'  => $href,
        'title' => $title !== '' ? $title : "Sans titre",
        'date'  => $date,
    ];
    $idx++;
}

// 4) Petite fonction utilitaire pour échapper facilement
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<section id="i-actualites-globales">
	<section class="i-ag-ligne-1">
		<figure class="i-ag-actu-1">
			<img src="<?= e($slots[1]['img']) ?>" alt="Actualité 1" loading="lazy" width="100%" height="400">
			<figcaption>
				<a href="<?= e($slots[1]['href']) ?>">
					<h3 class="limite-3lignes"><?= e($slots[1]['title']) ?></h3>
				</a>
				<div>
					<p class="date-publication"><?= e($slots[1]['date']) ?></p>
				</div>
			</figcaption>
		</figure>

		<figure class="i-ag-actu-2">
			<img src="<?= e($slots[2]['img']) ?>" alt="Actualité 2" loading="lazy" width="100%" height="400">
			<figcaption>
				<a href="<?= e($slots[2]['href']) ?>">
					<h3 class="limite-3lignes"><?= e($slots[2]['title']) ?></h3>
				</a>
				<div>
					<p class="date-publication"><?= e($slots[2]['date']) ?></p>
				</div>
			</figcaption>
		</figure>
	</section>

	<section class="i-ag-ligne-2">
		<button class="carousel-prev" aria-label="Précédent">&#10094;</button>
		<section class="carousel-track">
			<figure class="i-ag-actu-3">
				<img src="<?= e($slots[3]['img']) ?>" alt="Actualité 3" loading="lazy" width="100%" height="210">
				<figcaption>
					<a href="<?= e($slots[3]['href']) ?>">
						<h3 class="limite-3lignes"><?= e($slots[3]['title']) ?></h3>
					</a>
					<div>
						<p class="date-publication"><?= e($slots[3]['date']) ?></p>
					</div>
				</figcaption>
			</figure>
			<figure class="i-ag-actu-4">
				<img src="<?= e($slots[4]['img']) ?>" alt="Actualité 4" loading="lazy" width="100%" height="210">
				<figcaption>
					<a href="<?= e($slots[4]['href']) ?>">
						<h3 class="limite-3lignes"><?= e($slots[4]['title']) ?></h3>
					</a>
					<div>
						<p class="date-publication"><?= e($slots[4]['date']) ?></p>
					</div>
				</figcaption>
			</figure>
			<figure class="i-ag-actu-5">
				<img src="<?= e($slots[5]['img']) ?>" alt="Actualité 5" loading="lazy" width="100%" height="210">
				<figcaption>
					<a href="<?= e($slots[5]['href']) ?>">
						<h3 class="limite-3lignes"><?= e($slots[5]['title']) ?></h3>
					</a>
					<div>
						<p class="date-publication"><?= e($slots[5]['date']) ?></p>
					</div>
				</figcaption>
			</figure>
			<figure class="i-ag-actu-6">
				<img src="<?= e($slots[6]['img']) ?>" alt="Actualité 6" loading="lazy" width="100%" height="210">
				<figcaption>
					<a href="<?= e($slots[6]['href']) ?>">
						<h3 class="limite-3lignes"><?= e($slots[6]['title']) ?></h3>
					</a>
					<div>
						<p class="date-publication"><?= e($slots[6]['date']) ?></p>
					</div>
				</figcaption>
			</figure>
			<figure class="i-ag-actu-7">
				<img src="<?= e($slots[7]['img']) ?>" alt="Actualité 7" loading="lazy" width="100%" height="210">
				<figcaption>
					<a href="<?= e($slots[7]['href']) ?>">
						<h3 class="limite-3lignes"><?= e($slots[7]['title']) ?></h3>
					</a>
					<div>
						<p class="date-publication"><?= e($slots[7]['date']) ?></p>
					</div>
				</figcaption>
			</figure>
			<figure class="i-ag-actu-8">
				<img src="<?= e($slots[8]['img']) ?>" alt="Actualité 8" loading="lazy" width="100%" height="210">
				<figcaption>
					<a href="<?= e($slots[8]['href']) ?>">
						<h3 class="limite-3lignes"><?= e($slots[8]['title']) ?></h3>
					</a>
					<div>
						<p class="date-publication"><?= e($slots[8]['date']) ?></p>
					</div>
				</figcaption>
			</figure>
			<figure class="i-ag-actu-9">
				<img src="<?= e($slots[9]['img']) ?>" alt="Actualité 9" loading="lazy" width="100%" height="210">
				<figcaption>
					<a href="<?= e($slots[9]['href']) ?>">
						<h3 class="limite-3lignes"><?= e($slots[9]['title']) ?></h3>
					</a>
					<div>
						<p class="date-publication"><?= e($slots[9]['date']) ?></p>
					</div>
				</figcaption>
			</figure>
			<figure class="i-ag-actu-10">
				<img src="<?= e($slots[10]['img']) ?>" alt="Actualité 10" loading="lazy" width="100%" height="210">
				<figcaption>
					<a href="<?= e($slots[10]['href']) ?>">
						<h3 class="limite-3lignes"><?= e($slots[10]['title']) ?></h3>
					</a>
					<div>
						<p class="date-publication"><?= e($slots[10]['date']) ?></p>
					</div>
				</figcaption>
			</figure>
		</section>
		<button class="carousel-next">&#10095;</button>
	</section>
</section>
