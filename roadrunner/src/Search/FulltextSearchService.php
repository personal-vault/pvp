<?php

declare(strict_types=1);

namespace App\Search;

use App\Database;
use PDO;
use Psr\Log\LoggerInterface;

class FulltextSearchService
{
    private PDO $pdo;

    public function __construct(
        private Database $database,
        private LoggerInterface $logger
    ) {
        $this->pdo = $database->getPdo();
    }

    /**
     * @return SearchResult[]
     */
    public function runSearch(string $query): array
    {
        $search_sql = "SELECT
            files.id,
            files.path,
            files.mime,
            rank_path,
            rank_transcript,
            similarity,
            preview
        FROM
            files,
            to_tsvector(files.path || ' ' || files.transcript) document,
            to_tsquery(:query) query,
            ts_headline(files.path || ' ' || files.transcript, query) preview,
            NULLIF(ts_rank(to_tsvector(files.path), query), 0) rank_path,
            NULLIF(ts_rank(to_tsvector(files.transcript), query), 0) rank_transcript,
            SIMILARITY(:query, files.path || ' ' || files.transcript) similarity
        WHERE query @@ document OR similarity > 0
        ORDER BY rank_path DESC NULLS LAST, rank_transcript DESC NULLS LAST, similarity DESC NULLS LAST";
        $stmt = $this->pdo->prepare($search_sql);
        $stmt->bindValue(':query', $query, PDO::PARAM_STR);
        $stmt->execute();

        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = self::makeFromRow($row);
        }
        return $results;
    }

    private static function makeFromRow(array $row): SearchResult
    {
        $search_result = new SearchResult();
        $search_result->id = $row['id'];
        $search_result->path = $row['path'];
        $search_result->mime = $row['mime'];
        $search_result->preview = $row['preview'];
        $search_result->rank_path = $row['rank_path'];
        $search_result->rank_transcript = $row['rank_transcript'];
        $search_result->similarity = $row['similarity'];

        return $search_result;
    }
}
