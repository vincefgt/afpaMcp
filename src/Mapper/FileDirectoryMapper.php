<?php

declare(strict_types=1);

namespace App\Mapper;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Intl\Exception\MissingResourceException;

class FileDirectoryMapper
{
    /**
     * Domains
     *
     * @var string[]
     */
    private $domains;

    /**
     * Directories
     *
     * @var string[]
     */
    private $directories;

    /**
     * Directories
     *
     * @var string[]
     */
    private $directoriesDownload;

    /**
     * DomainMapper constructor.
     *
     * @param ParameterBagInterface $config
     */
    public function __construct(ParameterBagInterface $config)
    {
        $this->loadConfig($config);
    }

    /**
     * @param ParameterBagInterface $config
     *
     * @return void
     */
    private function loadConfig(ParameterBagInterface $config): void
    {
        $this->domains = $config->get('domains');
        $this->directories = $config->get('files_directory');
        $this->directoriesDownload = $config->get('files_directory_download');

        $array = ['domains', 'directories'];

        foreach ($array as $item) {
            if (empty($this->{$item})) {
                throw new MissingResourceException(sprintf('No %s found in domains.yml', $item));
            }
        }
    }

    /**
     * Get url of the services from a domain name
     *
     * @param string $domain
     *
     * @return string
     */
    public function getUploadDirectoryByDomain(string $domain): ?string
    {
        if (isset($this->domains[$domain]) && isset($this->directories[$this->domains[$domain]])) {
            return $this->directories[$this->domains[$domain]];
        }

        return null;
    }

    /**
     * Get url of the services from a domain name
     *
     * @param string $domain
     *
     * @return string
     */
    public function getDownloadDirectoryByDomain(string $domain): ?string
    {
        if (isset($this->domains[$domain]) && isset($this->directories[$this->domains[$domain]])) {
            return $this->directoriesDownload[$this->domains[$domain]];
        }

        return null;
    }
}
