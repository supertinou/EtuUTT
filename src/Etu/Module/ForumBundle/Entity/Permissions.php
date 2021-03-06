<?php

namespace Etu\Module\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Etu\Module\ForumBundle\Entity\Category;
use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;

/**
 * @ORM\Table(name="etu_forum_permissions")
 * @ORM\Entity
 */
class Permissions
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var Category $category
	 *
	 * @ORM\ManyToOne(targetEntity="Category")
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $category;
	
	/**
	 * @var Organization $organization
	 *
	 * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\Organization")
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $organization;

	/**
	 * @var User $user
	 *
	 * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $user;

	/**
	 * @var integer $read
	 *
	 * @ORM\Column(name="read", type="integer")
	 */
	protected $read;
	
	/**
	 * @var integer $post
	 *
	 * @ORM\Column(name="post", type="integer")
	 */
	protected $post;
	
	/**
	 * @var integer $answer
	 *
	 * @ORM\Column(name="answer", type="integer")
	 */
	protected $answer;
	
	/**
	 * @var integer $edit
	 *
	 * @ORM\Column(name="edit", type="integer")
	 */
	protected $edit;
	
	/**
	 * @var integer $sticky
	 *
	 * @ORM\Column(name="sticky", type="integer")
	 */
	protected $sticky;
	
	/**
	 * @var integer $lock
	 *
	 * @ORM\Column(name="lock", type="integer")
	 */
	protected $lock;
	
	/**
	 * @var integer $move
	 *
	 * @ORM\Column(name="move", type="integer")
	 */
	protected $move;

	/**
	 * @var integer $delete
	 *
	 * @ORM\Column(name="delete", type="integer")
	 */
	protected $delete;

	/**
	 * @var integer $type
	 *
	 * @ORM\Column(name="type", type="integer")
	 */
	protected $type;


	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->read = 0;
		$this->post = 0;
		$this->answer = 0;
		$this->edit = 0;
		$this->sticky = 0;
		$this->lock = 0;
		$this->move = 0;
		$this->type = 1;
	}
	
	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * @return int
	 */
	public function getRead()
	{
		return $this->read;
	}
	
	/**
	 * @return int
	 */
	public function getPost()
	{
		return $this->post;
	}
	
	/**
	 * @return int
	 */
	public function getAnswer()
	{
		return $this->answer;
	}
	
	/**
	 * @return int
	 */
	public function getEdit()
	{
		return $this->edit;
	}
	
	/**
	 * @return int
	 */
	public function getSticky()
	{
		return $this->sticky;
	}
	
	/**
	 * @return int
	 */
	public function getLock()
	{
		return $this->lock;
	}
	
	/**
	 * @return int
	 */
	public function getMove()
	{
		return $this->move;
	}

	/**
	 * @return int
	 */
	public function getDelete()
	{
		return $this->delete;
	}

	/**
	 * @return int
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return \Etu\Core\UserBundle\Entity\Organization
	 */
	public function getOrganization()
	{
		return $this->organization;
	}

	/**
	 * @return \Etu\Core\UserBundle\Entity\User
	 */
	public function getUser()
	{
		return $this->user;
	}
}
