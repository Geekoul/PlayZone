<?php
declare(strict_types=1); // Active le mode strict pour les types PHP (plus de sécurité)

namespace Tests\Unit; // Le test fait partie du namespace Tests\Unit

use App\Model\ArticleModel;        // On importe le modèle à tester
use PHPUnit\Framework\TestCase;    // Classe mère des tests PHPUnit

// Classe de test unitaire pour ArticleModel
final class ArticleModelTest extends TestCase
{
    public function test_slugExists_returns_true_when_row_found(): void
    {
        // --- 1) Création de mocks (objets simulés) ---

        // Mock de PDO pour empêcher toute connexion réelle à la base
        $pdo = $this->createMock(\PDO::class);

        // Mock de PDOStatement (résultat d'une requête préparée)
        $stmt = $this->createMock(\PDOStatement::class);

        // Quand ArticleModel appelle $pdo->prepare("..."), 
        // PHPUnit renverra notre faux statement
        $pdo->method('prepare')->willReturn($stmt);

        // Quand slugExists appelle $stmt->execute([...]),
        // on simule un succès (retourne true)
        $stmt->method('execute')->willReturn(true);

        // Quand slugExists appelle $stmt->fetchColumn(),
        // on simule qu’une ligne a été trouvée → retourne 1
        $stmt->method('fetchColumn')->willReturn(1);

        // --- 2) Instanciation du modèle avec le faux PDO ---
        $model = new ArticleModel($pdo);

        // --- 3) Appel de la méthode testée ---
        $result = $model->slugExists('mon-slug');

        // --- 4) Vérification du résultat attendu ---
        // Comme fetchColumn() renvoie 1, slugExists doit renvoyer true
        $this->assertTrue($result);
    }

    public function test_slugExists_returns_false_when_no_row(): void
    {
        // Nouveau mock PDO
        $pdo = $this->createMock(\PDO::class);

        // Nouveau mock PDOStatement
        $stmt = $this->createMock(\PDOStatement::class);

        // Comme dans le test précédent : prepare() → renvoie le faux statement
        $pdo->method('prepare')->willReturn($stmt);

        // execute() doit simuler un succès
        $stmt->method('execute')->willReturn(true);

        // Ici, on simule l'absence de résultat → fetchColumn() = false
        $stmt->method('fetchColumn')->willReturn(false);

        // On instancie encore ArticleModel avec ce PDO simulé
        $model = new ArticleModel($pdo);

        // On appelle la méthode testée
        $result = $model->slugExists('slug-inexistant');

        // Cette fois, on s’attend à false
        $this->assertFalse($result);
    }
}
