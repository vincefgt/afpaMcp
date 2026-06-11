<?php

declare(strict_types=1);

namespace App\Mapper;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Intl\Exception\MissingResourceException;

class DomainMapper
{
    /**
     * Domains
     *
     * @var string[]
     */
    private $domains;

    /**
     * Urls
     *
     * @var string[]
     */
    private $urls;

    /**
     * Uploads
     *
     * @var string[]
     */
    private $uploads;

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
     */
    private function loadConfig(ParameterBagInterface $config)
    {
        $this->domains = $config->get('domains');
        $this->urls = $config->get('urls');
        $this->uploads = $config->get('uploads');

        $array = ['domains', 'urls', 'uploads'];

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
     * @return string|null
     */
    public function getUrlByDomain(string $domain): ?string
    {
        if (isset($this->domains[$domain]) && isset($this->urls[$this->domains[$domain]])) {
            return $this->urls[$this->domains[$domain]];
        } elseif (isset($this->urls['default'])) {
            return $this->urls['default'];
        }

        return null;
    }

    /**
     * Get url of the services from a domain name
     *
     * @param string $domain
     *
     * @return string|null
     */
    public function getUploadByDomain(string $domain): ?string
    {
        if (isset($this->domains[$domain]) && isset($this->uploads[$this->domains[$domain]])) {
            return $this->uploads[$this->domains[$domain]];
        }

        return null;
    }
}
