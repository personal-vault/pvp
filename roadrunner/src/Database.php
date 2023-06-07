<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;
use Psr\Log\LoggerInterface;

class Database {

    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function getPdo(): PDO
    {
        try {
            // $this->logger->debug('Connecting to postgresql');
            $pdo = new PDO(
                sprintf("pgsql:host=%s;dbname=%s", $_ENV['POSTGRES_HOST'], $_ENV['POSTGRES_DB']),
                $_ENV['POSTGRES_USER'],
                $_ENV['POSTGRES_PASSWORD']
            );
            // $this->logger->debug('Connected to postgresql');
        } catch (PDOException $e) {
            // $this->logger->error($e->getMessage());
            throw $e;
        }

        return $pdo;
    }
}
