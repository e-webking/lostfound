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
class UserLike {

    /**
     * The users
     * @var \Incvisio\LostFound\Domain\Model\User
     * @ORM\ManyToOne(inversedBy="userLike")
     */
    protected $user;

    /**
     *
     *
     * @var string
     * @Flow\Validate(type="Text")
     * @Flow\Validate(type="StringLength", options={ "minimum"=1, "maximum"=250 })
     * @ORM\Column(length=250)
     */
    protected $likedUser;

    /**
     * @return string
     */
    public function getLikedUser() {
        return $this->likedUser;
    }

    /**
     * @param string $likedUser
     * @return void
     */
    public function setLikedUser($likedUser) {
        $this->likedUser = $likedUser;
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
}