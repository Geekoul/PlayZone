<?php
declare(strict_types=1); // Active le mode strict pour les types PHP (plus de sécurité)

namespace Tests\Unit; 

use App\Model\ArticleModel;
use PHPUnit\Framework\TestCase;    // Classe mère des tests PHPUnit

// Classe de test unitaire pour ArticleModel
final class ArticleModelTest extends TestCase
{
    // Teste que slugExists() renvoie true lorsqu'une ligne existe en base (fetchColumn = 1).
    public function test_slugExists_returns_true_when_row_found(): void
    {
        // --- 1) Création de mocks (objets simulés) ---
        $pdo = $this->createMock(\PDO::class);
        $stmt = $this->createMock(\PDOStatement::class);
        $pdo->method('prepare')->willReturn($stmt);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchColumn')->willReturn(1);

        // --- 2) Instanciation du modèle avec le faux PDO ---
        $model = new ArticleModel($pdo);

        // --- 3) Appel de la méthode testée ---
        $result = $model->slugExists('mon-slug');

        // --- 4) Vérification du résultat attendu ---
        // Comme fetchColumn() renvoie 1, slugExists doit renvoyer true
        $this->assertTrue($result);
    }

    // Teste que slugExists() renvoie false lorsqu'aucune ligne n'est trouvée (fetchColumn = false).
    public function test_slugExists_returns_false_when_no_row(): void
    {

        $pdo = $this->createMock(\PDO::class);
        $stmt = $this->createMock(\PDOStatement::class);
        $pdo->method('prepare')->willReturn($stmt);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchColumn')->willReturn(false);
        $model = new ArticleModel($pdo);

        $result = $model->slugExists('slug-inexistant');

        $this->assertFalse($result);
    }
}
