<?php

declare(strict_types=1);

namespace App\Search;

class SearchResult
{
    public $id;
    public $path;
    public $mime;
    public $rank_path;
    public $rank_transcript;
    public $similarity;
    public $preview;
}
