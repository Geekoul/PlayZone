<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Model\ArticleModel;
use PHPUnit\Framework\TestCase;

final class ArticleModelTest extends TestCase
{
    public function test_slugExists_returns_true_when_row_found(): void
    {
        // 1) On crée un mock de PDO et de PDOStatement
        $pdo = $this->createMock(\PDO::class);
        $stmt = $this->createMock(\PDOStatement::class);

        // Quand ArticleModel appelle $pdo->prepare(...),
        // on veut que ça retourne notre faux $stmt
        $pdo->method('prepare')->willReturn($stmt);

        // Quand slugExists appelle ->execute([...]),
        // on dit "OK, ça renvoie true"
        $stmt->method('execute')->willReturn(true);

        // Quand slugExists appelle ->fetchColumn(),
        // on simule une ligne trouvée (ici 1)
        $stmt->method('fetchColumn')->willReturn(1);

        $model = new ArticleModel($pdo);

        $result = $model->slugExists('mon-slug');

        // On s’attend à true puisque fetchColumn() a renvoyé 1
        $this->assertTrue($result);
    }

    public function test_slugExists_returns_false_when_no_row(): void
    {
        $pdo = $this->createMock(\PDO::class);
        $stmt = $this->createMock(\PDOStatement::class);

        $pdo->method('prepare')->willReturn($stmt);
        $stmt->method('execute')->willReturn(true);

        // Ici on simule "aucun résultat" -> fetchColumn = false
        $stmt->method('fetchColumn')->willReturn(false);

        $model = new ArticleModel($pdo);

        $result = $model->slugExists('slug-inexistant');

        $this->assertFalse($result);
    }
}
