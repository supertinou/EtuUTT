<?php

namespace Etu\Core\UserBundle\CriApi\Browser;

use Buzz\Message\MessageInterface;

/**
 * CRI's server browser.
 */
class CriBrowser
{
	const ROOT_URL = 'http://alambix2-dev.utt.fr/test/apietu/web/index.php';

    /**
     * @param string $path
     * @param array $parameters
     * @return MessageInterface
     */
    public function request($path, array $parameters = array())
	{
        $browser = new \Buzz\Browser();

		$get = [];

		foreach ($parameters as $key => $value) {
			$get[] = $key . '=' . $value;
		}

        return $browser->get(self::ROOT_URL . $path . '?' . implode('&', $get));
	}
}
