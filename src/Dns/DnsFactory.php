<?php

namespace LibCloud\Dns;

class DnsFactory
{
    /**
     * @param string $type provider name
     * @param string $accessToken api token
     * @param string $accessTokenSecret api token secret password
     * @return \LibCloud\Dns\DnsInterface
     * @throws \Exception
     */
    public static function getProvider($type, $accessToken, $accessTokenSecret)
    {
        try {
            $namePart = implode(array_map('ucfirst', explode('_', $type)));
            $className = 'LibCloud\Dns\Providers\\'.$namePart.'\\'.$namePart.'Provider';
            return new $className($accessToken, $accessTokenSecret);
        }
        catch (\Exception $e) {
            throw new \Exception(sprintf(
                "Class for '%s' not found",
                $type
            ));
        }
    }
}
