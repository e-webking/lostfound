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
class User  extends \TYPO3\Party\Domain\Model\Person{

    /**
     * The $like.
     *
     * @var int
     */
    protected $likes;
    /**
     * The $dislike.
     *
     * @var int
     */
    protected $dislike;
    /**
     * The name.
     *
     * @var string
     */
    protected $socialPhoto='';

    /**
     * The name.
     *
     * @var string
     */
    protected $socialNetwork='';

    /**
     * The name.
     *
     * @var string
     */
    protected $socialEmail='';

    /**
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Incvisio\LostFound\Domain\Model\Post>
     * @ORM\OneToMany(mappedBy="user")
     */
    protected $posts;

    /**
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Incvisio\LostFound\Domain\Model\UserLike>
     * @ORM\OneToMany(mappedBy="user")
     */
    protected $userLike;

    /**
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Incvisio\LostFound\Domain\Model\CommentLike>
     * @ORM\OneToMany(mappedBy="user")
     */
    protected $commentLike;

    /**
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Incvisio\LostFound\Domain\Model\Comments>
     * @ORM\OneToMany(mappedBy="user")
     */
    protected $comments;

    /**
     * @return string
     */
    public function getSocialPhoto() {
        return $this->socialPhoto;
    }

    /**
     * @param string $socialPhoto
     * @return void
     */
    public function setSocialPhoto($socialPhoto) {
        $this->socialPhoto = $socialPhoto;
    }

    /**
     * @return string
     */
    public function getSocialEmail() {
        return $this->socialEmail;
    }

    /**
     * @param string $socialEmail
     * @return void
     */
    public function setSocialEmail($socialEmail) {
        $this->socialEmail = $socialEmail;
    }
    /**
     * @return string
     */
    public function getSocialNetwork() {
        return $this->socialNetwork;
    }

    /**
     * @param string $socialNetwork
     * @return void
     */
    public function setSocialNetwork($socialNetwork) {
        $this->socialNetwork = $socialNetwork;
    }

    /**
     * @return int
     */
    public function getLikes() {
        return $this->likes;
    }

    /**
     * @param int $likes
     * @return void
     */
    public function setLikes($likes) {
        $this->likes = $likes;
    }

    /**
     * @return int
     */
    public function getDislike() {
        return $this->dislike;
    }

    /**
     * @param int $dislike
     * @return void
     */
    public function setDislike($dislike) {
        $this->dislike = $dislike;
    }
}