<?php

namespace Etu\Module\UVBundle\CriApi;

use Etu\Core\UserBundle\CriApi\Browser\CriBrowser;

/**
 * Schedules manager based on CRI-hosted API.
 */
class UvApi
{
	/**
	 * @var CriBrowser
	 */
	protected $browser;

    /**
     * @param CriBrowser $browser
     */
    public function __construct(CriBrowser $browser)
	{
		$this->browser = $browser;
	}

	/**
	 * @param integer $page
	 * @return bool
	 */
	public function findPage($page)
	{
		$result = json_decode($this->browser->request('/uvs', [ 'page' => $page ])->getContent(), true);

		$courses = array();

		foreach ($result['data'] as $values) {
			$courses[] = $values;
		}

		return array(
			'pagination' => $result['pagination'],
			'courses' => $courses,
		);
	}
}
