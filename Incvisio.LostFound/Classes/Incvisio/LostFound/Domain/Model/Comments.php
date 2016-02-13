<?php
namespace Incvisio\LostFound\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Incvisio.LostFound".    *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class Comments {

    /**
     * The Advert
     * @var \Incvisio\LostFound\Domain\Model\Post
     * @ORM\ManyToOne(inversedBy="comments")
     */
    protected $post;

    /**
     * @var \DateTime
     */
    protected $publishDateTime;

    /**
     * message.
     *
     * @var string
     * @Flow\Validate(type="Text")
     * @ORM\Column(type="text")
     * @ORM\Column(length=2000)
     */
    protected $message;

    /**
     * The users
     * @var \Incvisio\LostFound\Domain\Model\User
     * @ORM\ManyToOne(inversedBy="comments")
     */
    protected $user;

    /**
     * @var int
     */
    protected $likes;

    /**
     * @var int
     */
    protected $dislikes;

    /**
     * Sets Advert.
     *
     * @param \Incvisio\LostFound\Domain\Model\Post $post
     * @return void
     */
    public function setPost($post) {
        $this->post = $post;
    }

    /**
     * Returns Advert.
     *
     * @return \Incvisio\LostFound\Domain\Model\Post
     */
    public function getPost() {
        return  $this->post;
    }

    /**
     * @return string
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * @param string $message
     * @return void
     */
    public function setMessage($message) {
        $this->message = $message;
    }

    /**
     * @param \DateTime
     * @return void
     */
    public function setPublishDate($publishDateTime) {
        $this->publishDateTime = $publishDateTime;
    }

    /**
     * @return \DateTime
     */
    public function getPublishDate() {
        return $this->publishDateTime;
    }

    /**
     * Sets user.
     *
     * @param \Incvisio\LostFound\Domain\Model\User $user
     * @return void
     */
    public function setUser($user) {
        $this->user = $user;
    }

    /**
     * Returns user.
     *
     * @return \Incvisio\LostFound\Domain\Model\User
     */
    public function getUser() {
        return  $this->user;
    }

    /**
     * Sets like
     *
     * @param int $likes
     * @return void
     */
    public function setLikes($likes) {
        $this->likes = $likes;
    }

    /**
     * Likes
     *
     * @return int
     */
    public function getLikes() {
        return $this->likes;
    }

    /**
     * Dislikes
     *
     * @param int $dislikes
     * @return void
     */
    public function setDislikes($dislikes) {
        $this->dislikes = $dislikes;
    }

    /**
     * Dislikes
     *
     * @return int
     */
    public function getDislikes() {
        return $this->dislikes;
    }
}