<?php

namespace App\ResumesFinder;

use GuzzleHttp\Exception\ClientException;

use function App\Data\saveFileContents;

use function App\Http\requestAndGetContents;

const LINKS_FILENAME = 'resumes_list.json';

function run()
{
    $xml = requestResumesXml();

    $rssFeedContent = simplexml_load_string($xml);

    $resumes = [];
    foreach ($rssFeedContent->channel->item as $item) {
        $resumes[] = createResume($item);
    }

    saveFileContents(LINKS_FILENAME, $resumes);
}

function createResume($item): array
{
    return [
        'title' => (string)$item->title,
        'description' => (string)$item->description,
        'link' => (string)$item->link,
    ];
}

function requestResumesXml(): string
{
    try {
        $content = requestAndGetContents('resumes.rss');
    } catch (ClientException) {
        throw new \Exception("Can't get RSS.");
    }

    return $content;
}
