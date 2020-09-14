<?php

class Instagram {
    /**
     * Make a request that in every aspect possible looks like one made by a browser
     * 
     * @param string $url URL to request 
     * @throws Exception If the request could not be executed
     * @return string|false
     */
    public static function getUrl($url) {
        // Initialize cURL
        $ch = curl_init();

        // Get a random user agent
        $agent = \Campo\UserAgent::random([
            'os_type' => ['Windows', 'Android', 'iOS', 'Linux', 'OS X', 'Firefox OS'],
            'agent_type' => ['Browser']
        ]);

        // Set cURL options
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_REFERER => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => $agent,
            CURLOPT_ENCODING => 'gzip, deflate',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,' . 
                'image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'Accept-Language: en-US,en;q=0.5',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1'
            ]
        ]);

        // Execute the request and get response
        $response = curl_exec($ch);

        // Throw an error if we could not execute the request
        if ($response === false) {
            throw new Exception(curl_error($ch));
        }

        // Close cURL connection
        curl_close($ch);

        // Return the response content
        return $response;
    }

    /**
     * Query a nested array using dot notation syntax.
     * 
     * @param array $data
     * @param string|null $path
     * @return mixed
     */
    private static function arrayGet($array, $path = '')
    {
        if (empty($path)) {
            return $array;
        }

        $keys = explode('.', $path);

        $structure = $array;

        foreach ($keys as $key) {
            if (isset($structure[$key])) {
                $structure = $structure[$key];
            } else {
                return false;
            }
        }

        return $structure;
    }

    /**
     * General scraper method (used for both user and tag searches)
     * Takes a $path using dot notation syntax to return a specific level in the
     * response array (if any).
     * 
     * Returns an array of items, with an optional limit, and false on errors or
     * when no items are found.
     * 
     * @param string $url
     * @param string $path
     * @param int $limit
     * @return array|false
     */
    private static function scrape($url, $path, $limit = null)
    {
        try {
            $feed = self::getUrl($url);
        } catch (Exception $e) {
            return false;
        }

        if (!$feed) {
            return false;
        }

        $data = explode('window._sharedData = ', $feed);

        if (!isset($data[1])) {
            return false;
        }

        $data_json = explode(';</script>', $data[1]);
        $data_obj = json_decode($data_json[0], true);

        if (!($data_obj && !empty($data_obj['entry_data']))) {
            return false;
        }

        $structure = self::arrayGet($data_obj['entry_data'], $path);

        if (!($structure && isset($structure['edges']))) {
            return false;
        }

        $media = $structure['edges'];
        $items = [];

        if (!empty($media) && is_array($media)) {
            foreach ($media as $item) {
                $item = $item['node'];

                $items[] = [
                    'image'    => $item['display_url'],
                    'url'      => 'https://www.instagram.com/p/' . $item['shortcode'] . '/',
                    'likes'    => $item['edge_liked_by']['count'],
                    'comments' => $item['edge_media_to_comment']['count']
                ];
            }
        }

        return $limit !== null ? array_slice($items, 0, $limit) : $items;
    }

    /**
     * Get public users media, with an optional limit.
     * 
     * Returns false on errors, or when no items are found.
     * 
     * @param string $tag
     * @param int|null $limit
     * @return array|false
     */
    public static function getUser($user, $limit = null)
    {
        return self::scrape(
            'https://www.instagram.com/' . $user . '/',
            'ProfilePage.0.graphql.user.edge_owner_to_timeline_media',
            $limit
        );
    }

    /**
     * Get public media with specified tag, with an optional limit.
     * 
     * Returns false on errors, or when no items are found.
     * 
     * @param string $tag
     * @param int|null $limit
     * @return array|false
     */
    public static function getTag($tag, $limit = null)
    {
        return self::scrape(
            'https://www.instagram.com/explore/tags/' . $tag . '/',
            'TagPage.0.graphql.hashtag.edge_hashtag_to_media',
            $limit
        );
    }
}
