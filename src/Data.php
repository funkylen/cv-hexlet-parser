<?php

namespace App\Data;

const FOLDER = __DIR__ . '/../resumes/';

function save(string $name, array $data)
{
    $filename = FOLDER . $name;

    file_put_contents($filename, json_encode($data, JSON_UNESCAPED_UNICODE));
}

function get(string $name)
{
    $path = FOLDER . $name;

    if (!file_exists($path)) {
        throw new \Exception("No file $path");
    }

    return json_decode(file_get_contents($path), true);
}