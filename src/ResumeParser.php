<?php

namespace App\ResumeParser;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\DomCrawler\Crawler;

use function App\Data\get;
use function App\Data\save;

use const App\App\URL;
use const App\ResumesFinder\LINKS_FILENAME;

const RESUMES_FILENAME = 'resumes.json';

const HTML_NAME_TO_NODE_NAME_MAP
= [
    'описание' => 'description',
    'имя' => 'name',
    'навыки' => 'skills',
    'награды, сертификаты' => 'rewards',
    'владение английским' => 'englishSkills',
    'hexlet' => 'hexletLink',
    'github' => 'githubLink',
    'контакт' => 'contact',
];

function run()
{
    $allowedHtmlNames = array_keys(HTML_NAME_TO_NODE_NAME_MAP);

    $resumesLinksList = getResumesLinksList();

    $resumes = [];
    foreach ($resumesLinksList as $item) {
        $html = requestHtml($item['link']);

        if ($html === null) {
            continue;
        }

        $crawler = new Crawler($html);

        $title = $crawler->filter('h1')->getNode(0)->textContent;

        $nodeData = ['title' => $title];

        /**
         * NOTE: Can be corrupted with HTML structure changes
         */
        $crawler
            ->filter('main > .row > .col-md-9 > div.mb-5 > div.row')
            ->reduce(function (Crawler $childNode) use ($allowedHtmlNames) {
                $rowTitle = $childNode->children()->getNode(0)->textContent;
                $normalizedRowTitle = mb_strtolower(trim($rowTitle));
                return in_array($normalizedRowTitle, $allowedHtmlNames, true);
            })
            ->each(
                function (Crawler $childNode) use (&$nodeData) {
                    $children = $childNode->children();
                    $rowName = $children->getNode(0)?->textContent;
                    if ($rowName === null) {
                        return;
                    }

                    $rowContent = $children->getNode(1)?->textContent;

                    $normalizedRowName = mb_strtolower(trim($rowName));
                    $key = HTML_NAME_TO_NODE_NAME_MAP[$normalizedRowName];
                    $nodeData[$key] = trim($rowContent);
                }
            );

        // TODO: Parse Jobs
        // TODO: Parse Education

        $resumes[] = createResumeNode($nodeData);

        /**
         * NOTE: To avoid ban
         */
        sleep(10);

        if (count($resumes) > 10) {
            save(RESUMES_FILENAME, $resumes);
            return;
        }
    }
}

function createResumeNode($data): array
{
    return [
        'title' => $data['title'] ?? null,
        'name' => $data['name'] ?? null,
        'description' => $data['description'] ?? null,
        'skills' => $data['skills'] ?? null,
        'rewards' => $data['rewards'] ?? null,
        'englishSkills' => $data['englishSkills'] ?? null,
        'hexletLink' => $data['hexletLink'] ?? null,
        'githubLink' => $data['githubLink'] ?? null,
    ];
}

function requestHtml(string $link): ?string
{
    try {
        $client = new Client(['base_uri' => URL]);

        $response = $client->request('GET', $link);
    } catch (ClientException) {
        return null;
    }

    return $response->getBody()->getContents();
}

function getResumesLinksList()
{
    return get(LINKS_FILENAME);
}