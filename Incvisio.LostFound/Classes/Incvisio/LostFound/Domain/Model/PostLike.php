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
class PostLike { 

    /**
     * The users
     * @var \Incvisio\LostFound\Domain\Model\User
     * @ORM\ManyToOne(inversedBy="postLike")
     */
    protected $user;
    
    /**
     * @var string
     * @Flow\Validate(type="Text")
     * @Flow\Validate(type="StringLength", options={ "minimum"=1, "maximum"=250 })
     * @ORM\Column(length=250)
     */
    protected $likedPost;


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
     * @return string
     */
    public function getLikedPost() {
        return $this->likedPost;
    }

    /**
     * @param string $liked
     * @return void
     */
    public function setLikedPost($liked) {
        $this->likedPost = $liked;
    }
}