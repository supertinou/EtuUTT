<?php

namespace Etu\Core\UserBundle\CriApi\Schedule;

use Etu\Core\UserBundle\CriApi\Browser\CriBrowser;
use Etu\Core\UserBundle\CriApi\Schedule\Model\Course;

/**
 * Schedules manager based on CRI-hosted API.
 */
class ScheduleApi
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
		$result = json_decode($this->browser->request('', [ 'page' => $page ])->getContent());

		$courses = array();

		foreach ($result->body->courses as $values) {
			$courses[] = new Course($values);
		}

		return array(
			'paging' => $result->body->paging,
			'courses' => $courses,
		);
	}
}
