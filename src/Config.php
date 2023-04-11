<?php

namespace Villermen\Toolbox;

use Symfony\Component\Yaml\Yaml;

class Config
{
    public string $publicProtocol;

    public string $publicHost;

    public string $publicPath;

    public ?string $googleClientId;

    public ?string $googleClientSecret;

    public static function load(): self
    {
        return new self(Yaml::parseFile('config.yaml'));
    }

    private function __construct(array $config)
    {
        $this->publicProtocol = empty($_SERVER['HTTPS']) ? 'http' : 'https';
        $this->publicHost = $_SERVER['HTTP_HOST'];
        $this->publicPath = sprintf('/%s', trim($config['publicPath'] ?? '', '/'));
        if ($this->publicPath !== '/') {
            $this->publicPath = sprintf('%s/', $this->publicPath);
        }

        $this->googleClientId = $config['googleClientId'] ?? null;
        $this->googleClientSecret = $config['googleClientSecret'] ?? null;
    }

    public function createUrl(string $path, ?array $query = null): string
    {
        $publicPath = $this->createPath($path, $query);
        return sprintf(
            '%s://%s%s',
            $this->publicProtocol,
            $this->publicHost,
            $publicPath,
        );
    }

    public function createPath(string $path, ?array $query = null): string
    {
        $queryString = ($query ? sprintf('?%s', http_build_query($query)) : '');
        return sprintf('%s%s%s', $this->publicPath, ltrim($path, '/'), $queryString);
    }
}
