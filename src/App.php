<?php

namespace App\App;

use function App\ResumeParser\run as runParser;
use function App\ResumesFinder\run as runFinder;

function findResumesLinks()
{
    runFinder();
}

function parseResumes()
{
    runParser();
}
