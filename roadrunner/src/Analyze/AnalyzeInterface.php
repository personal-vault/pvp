<?php

namespace App\Analyze;

use App\Model\File;

interface AnalyzeInterface
{
    public function analyze(File $file): ?string;
}
