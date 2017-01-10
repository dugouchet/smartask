<?php

namespace SMARTASK\CommentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\CommentBundle\Entity\Comment as BaseComment;

/**
 * @ORM\Entity
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
/**
 * Comment
 *
 * @ORM\Table(name="comment")
 * @ORM\Entity(repositoryClass="SMARTASK\CommentBundle\Repository\CommentRepository")
 */
class Comment extends BaseComment
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
	protected $id;

     /**
     * Thread of this comment
     *
     * @var Thread
     * @ORM\ManyToOne(targetEntity="SMARTASK\CommentBundle\Entity\Thread")
     */
    protected $thread;

}

