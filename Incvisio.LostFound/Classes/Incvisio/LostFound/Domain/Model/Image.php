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
class Image {

    /**
     * @var string
     */
    protected $imgTitle;

    /**
     * The Post
     * @var \Incvisio\LostFound\Domain\Model\Post
     * @ORM\ManyToOne(inversedBy="images")
     */
    protected $post;

    public function getPersistenceObjectIdentifier(){
        return $this->Persistence_Object_Identifier;
    }

    /**
     * @param string $imgTitle
     * @return void
     */
    public function setImgTitle($imgTitle) {
        $this->imgTitle = $imgTitle;
    }

    /**
     * @return string
     */
    public function getImgTitle() {
        return $this->imgTitle;
    }

    /**
     * Sets Post.
     *
     * @param \Incvisio\LostFound\Domain\Model\Post $post
     * @return void
     */
    public function setPost($post) {
        $this->post = $post;
    }

    /**
     * Returns Post.
     *
     * @return \Incvisio\LostFound\Domain\Model\Post
     */
    public function getPost() {
        return  $this->post;
    }
}