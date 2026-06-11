<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Entity\KeyCache;

class KeyCacheSerializer
{

    /**
     * Serialize a KeyCache and return the string hash
     *
     * @param KeyCache $key
     * @return string
     */
    public function serialize(KeyCache $key) : string
    {
        return md5(serialize($key));
    }
}
