<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Github
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function ownerExists(string $owner): bool
    {
        $user = $this->fetch(sprintf('https://api.github.com/users/%s', $owner), 36000);

        if (array_key_exists('login', (array)$user) && $user->login === $owner) {
            return true;
        }

        return false;
    }

    private function fetch(string $url, int $duration = 3600): object
    {
        $cache = new FilesystemAdapter();

        $value = $cache->get(
            sprintf('cache_%s', md5($url)),
            function (ItemInterface $item) use ($duration, $url) {
                $item->expiresAfter($duration);

                $response = $this->client->request(
                    'GET',
                    $url,
                    ['headers' => ['Accept' => 'application/vnd.github.v3+json']],
                );

                return json_decode($response->getContent(false));
            }
        );

        return $value;
    }
}
