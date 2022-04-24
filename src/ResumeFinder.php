<?php

namespace App\ResumesFinder;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

use function App\Data\save;

use const App\App\URL;

const LINKS_FILENAME = 'resumes_list.json';

function run()
{
    $xml = requestResumesXml();

    $rssFeedContent = simplexml_load_string($xml);

    $resumes = [];
    foreach ($rssFeedContent->channel->item as $item) {
        $resumes[] = createResume($item);
    }

    saveResumesList($resumes);
}

function createResume($item)
{
    return [
        'title' => (string)$item->title,
        'description' => (string)$item->description,
        'link' => (string)$item->link,
    ];
}

function requestResumesXml()
{
    try {
        $client = new Client(['base_uri' => URL]);

        $response = $client->request('GET', 'resumes.rss');
    } catch (ClientException) {
        throw new \Exception("Can't get RSS.");
    }

    return $response->getBody();
}

function saveResumesList(array $resumes)
{
    save(LINKS_FILENAME, $resumes);
}
