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
class Post {

    /**
     * @var string
     * @Flow\Validate(type="Text")
     * @Flow\Validate(type="StringLength", options={ "minimum"=1, "maximum"=250 })
     * @ORM\Column(length=250)
     */
    protected $title;

    /**
     * @var string
     * @Flow\Validate(type="Text")
     * @Flow\Validate(type="StringLength", options={ "minimum"=1, "maximum"=250 })
     * @ORM\Column(length=250)
     */
    protected $city;

    /**
     * @var string
     * @Flow\Validate(type="Text")
     * @ORM\Column(length=250)
     */
    protected $description;

    /**
     * @var string
     * @Flow\Validate(type="Text")
     * @ORM\Column(length=250)
     */
    protected $place;

    /**
     * @var \DateTime
     * @ORM\Column(nullable=true)
     * @ORM\Column(type="date")
     */
    protected $dateLostOrFound;

    /**
     * @var string
     * @ORM\Column(nullable=true)
     * @ORM\Column(length=250)
     */
    protected $timeLostOrFound;

    /**
     * @var \DateTime
     */
    protected $publishDate;

    /**
     *
     * @var \Incvisio\LostFound\Domain\Model\Category
     * @ORM\ManyToOne
     */
    protected $category;

    /**
     * @var string
     * @Flow\Validate(type="Text")
     * @ORM\Column(nullable=true)
     * @ORM\Column(length=250)
     */
    protected $userContacts;

    /**
     * The users
     * @var \Incvisio\LostFound\Domain\Model\User
     * @ORM\ManyToOne(inversedBy="posts")
     */
    protected $user;

    /**
     * @var int
     */
    protected $lost;

    /**
     * @var int
     */
    protected $found;

    /**
     * @var int
     */
    protected $active;

    /**
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Incvisio\LostFound\Domain\Model\Comments>
     * @ORM\OneToMany(mappedBy="post")
     * @ORM\OrderBy({"publishDateTime" = "DESC"})
     */
    protected $comments;

    /**
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Incvisio\LostFound\Domain\Model\Image>
     * @ORM\OneToMany(mappedBy="post")
     */
    protected $images;

    public function getPersistenceObjectIdentifier(){
        return $this->Persistence_Object_Identifier;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @param string $title
     * @return void
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getCity() {
        return $this->city;
    }

    /**
     * @param string $city
     * @return void
     */
    public function setCity($city) {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param string $description
     * @return void
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getPlace() {
        return $this->place;
    }

    /**
     * @param string $place
     * @return void
     */
    public function setPlace($place) {
        $this->place = $place;
    }

    /**
     * @param \DateTime
     * @return void
     */
    public function setDateLostOrFound($dateLostOrFound) {
        $this->dateLostOrFound = $dateLostOrFound;
    }

    /**
     * @return Date
     */
    public function getDateLostOrFound() {
        return $this->dateLostOrFound;
    }

    /**
     * @param string $timeLostOrFound
     * @return void
     */
    public function setTimeLostOrFound($timeLostOrFound) {
        $this->timeLostOrFound = $timeLostOrFound;
    }

    /**
     * @return string
     */
    public function getTimeLostOrFound() {
        return $this->timeLostOrFound;
    }

    /**
     * @param \DateTime
     * @return void
     */
    public function setPublishDate($publishDate) {
        $this->publishDate = $publishDate;
    }

    /**
     * @return Date
     */
    public function getPublishDate() {
        return $this->publishDate;
    }

    /**
     * Sets category.
     *
     * @param \Incvisio\LostFound\Domain\Model\Category $category
     * @return void
     */
    public function setCategory($category) {
        $this->category = $category;
    }

    /**
     * Returns category.
     *
     * @return \Incvisio\LostFound\Domain\Model\Category
     */
    public function getCategory() {
        return  $this->category;
    }

    /**
     * @return string
     */
    public function getUserContacts() {
        return $this->userContacts;
    }

    /**
     * @param string $userContacts
     * @return void
     */
    public function setUserContacts($userContacts) {
        $this->userContacts = $userContacts;
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
     * Sets the active status
     *
     * @param int $lost
     * @return void
     */
    public function setLost($lost) {
        $this->lost = $lost;
    }

    /**
     * Tells if this advert is Found
     *
     * @return int
     */
    public function getLost() {
        return $this->lost;
    }

    /**
     * Sets the active status
     *
     * @param int $found
     * @return void
     */
    public function setFound($found) {
        $this->found = $found;
    }

    /**
     * Tells if this advert is Found
     *
     * @return int
     */
    public function getFound() {
        return $this->found;
    }

    /**
     * Sets the active status
     *
     * @param int $active
     * @return void
     */
    public function setActive($active) {
        $this->active = $active;
    }

    /**
     * Tells if this advert is active
     *
     * @return int
     */
    public function getActive() {
        return $this->active;
    }

    public function getComments()
    {
        return $this->comments;
    }
}