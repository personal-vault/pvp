<?php declare(strict_types=1);

namespace Test;

use App\Database;
use App\ServiceProvider;
use League\Container\Container;
use League\Container\ReflectionContainer;
use PDO;
use PHPUnit\Framework\TestCase as FrameworkTestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class TestCase extends FrameworkTestCase
{
    protected Container $container;

    public function setUp(): void
    {
        $this->container = new Container();
        $this->container->delegate(new ReflectionContainer());
        $this->container->addServiceProvider(new ServiceProvider);
        $this->container->add(LoggerInterface::class, new NullLogger());
    }

    protected function selectFirstWhere(string $table, array $where): ?array
    {
        $rows = $this->selectWhere($table, $where);

        if (count($rows) === 0) {
            return null;
        }

        return $rows[0];
    }

    protected function selectWhere(string $table, array $where): array
    {
        $database = $this->container->get(Database::class);
        $pdo = $database->getPdo();

        $query = "SELECT * FROM $table WHERE ";
        $where_keys = array_keys($where);
        $where_values = array_values($where);

        $where_clauses = [];
        foreach ($where_keys as $key => $where_key) {
            $where_clauses[] = "$where_key = :$where_key";
        }
        $query .= implode(' AND ', $where_clauses);

        $stmt = $pdo->prepare($query);

        foreach ($where_keys as $key => $where_key) {
            $stmt->bindValue(":$where_key", $where_values[$key]);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
