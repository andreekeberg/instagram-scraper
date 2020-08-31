<?php

class Instagram {
    /**
     * Wrapper for file_get_contents that throws an ErrorException
     * instead of triggering a warning on errors.
     * 
     * @param mixed ...$args
     * @throws ErrorException
     * @return string|false
     */
	private static function getContents(...$args)
    {
        set_error_handler(
            function ($severity, $message, $file, $line) {
                throw new ErrorException($message, $severity, $severity, $file, $line);
            }
        );

        try {
            return call_user_func_array('file_get_contents', func_get_args());
        } catch (ErrorException $e) {
            restore_error_handler();

            throw $e;
        }
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
            $feed = self::getContents($url);
        } catch (ErrorException $e) {
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
