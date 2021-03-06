<?php

namespace Incvisio\LostFound\Controller;

/*
 * This script belongs to the TYPO3 Flow package "Incvisio.LostFound". *
 * *
 */
use TYPO3\Flow\Annotations as Flow;
use Incvisio\LostFound\Domain\Model\Post;
use Incvisio\LostFound\Domain\Model\UserLike;
use Incvisio\LostFound\Domain\Model\CommentLike;
use Incvisio\LostFound\Domain\Model\Comments;
use Incvisio\LostFound\Controller\MainController;
use TYPO3\Flow\Utility\Now;
use TYPO3\Flow\Error\Message;
use Incvisio\LostFound\Domain\Model\PostLike;

class PostController extends MainController {
	
	/**
	 *
	 * @var number @Flow\Inject(setting="strategies.Crop.comment")
	 */
	protected $commentcrop;
	
	/**
	 * @Flow\Inject
	 * 
	 * @var \Incvisio\LostFound\Domain\Repository\PostRepository
	 */
	protected $postRepository;
	
	/**
	 * @Flow\Inject
	 * 
	 * @var \Incvisio\LostFound\Domain\Repository\CategoryRepository
	 */
	protected $categoryRepository;
	
	/**
	 * @Flow\Inject
	 * 
	 * @var \Incvisio\LostFound\Domain\Repository\CommentsRepository
	 */
	protected $commentsRepository;
	
	/**
	 * @Flow\Inject
	 * 
	 * @var \Incvisio\LostFound\Domain\Repository\UserRepository
	 */
	protected $userRepository;
	
	/**
	 * @Flow\Inject
	 * 
	 * @var \Incvisio\LostFound\Domain\Repository\UserLikeRepository
	 */
	protected $userLikeRepository;
	
	/**
	 * @Flow\Inject
	 * 
	 * @var \Incvisio\LostFound\Domain\Repository\CommentLikeRepository
	 */
	protected $commentLikeRepository;
	
	/**
	 * @Flow\Inject
	 * 
	 * @var \Incvisio\LostFound\Domain\Repository\ImageRepository
	 */
	protected $imageRepository;
	
	/**
	 * @Flow\Inject
	 * 
	 * @var \Incvisio\LostFound\Domain\Repository\PostLikeRepository
	 */
	protected $postLikeRepository;
	
	/**
	 * @Flow\Inject
	 * 
	 * @var \TYPO3\Flow\Resource\ResourceManager
	 */
	protected $resourceManager;
	
	/**
	 * 
	 * @var \number
	 */
	protected $thumbSize = 150;
	
	/**
	 *
	 * @param string $type        	
	 * @return void
	 */
	public function indexAction($type = NUll) {
		
		$args = $this->request->getArguments();
		
		if ($type == "lost") {
			
			if (isset( $args['lost_input'] )) {
				$search_field = $args['lost_input'];
				$this->view->assign( 'search_field',  $search_field);
				$this->getPostsAction( $search_field, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'lost' );
				
				$count = $this->postRepository->countLost( $search_field );
				$this->view->assign('count', $count);
				if ($count['number'] == 0) {
					$this->redirectToUri( "/post?type=lost" );
				}
			} else {
				$posts = $this->postRepository->findByLost ( 1 );
				$this->view->assign('posts', $posts );
			}
		}
		if ($type == "found") {
			
			if (isset( $args['found_input'] )) {
				$search_field = $args['found_input'];
				$this->view->assign('search_field',  $search_field);
				$this->getPostsAction( NULL, $search_field, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'found' );
				$count = $this->postRepository->countFound($search_field);
				$this->view->assign('count', $count);
				if ($count['number'] == 0) {
					$this->redirectToUri ( "/post?type=found" );
				}
			} else {
				$posts = $this->postRepository->findByFound( 1 );
				$this->view->assign( 'posts', $posts );
			}
		}
		
		$facebook_appid = $this->facebookService->getAppId();
		$facebook_redirecturi = $this->facebookService->getRedirectUri ();
		
		$this->view->assignMultiple ( array (
				'facebook_appid' => $facebook_appid,
				'facebook_redirecturi' => $facebook_redirecturi 
		) );
		
		$this->view->assign( 'categories', $this->categoryRepository->findAll() );
		
		$this->view->assign( 'advertType', $type );
	}
	
	/**
	 *
	 * @param string $post        	
	 * @return void
	 */
	public function showAction($post) {
		$currentPost = $this->postRepository->findByIdentifier ( $post );
		$images = $this->imageRepository->findByPost ( $currentPost );
		
		if ($this->securityContext->getAccount () != NULL) {
			$this->view->assign ( 'currentUserInd', $this->getCurrentUser ()->getAccounts () );
		} else {
			$this->view->assign ( 'currentUserInd', '' );
		}
		
		if ($images->getFirst () != NULL) {
			$images = $images;
			$noImage = "False";
		} else {
			$images = NULL;
			$noImage = "True";
		}
		
		$charCrop = (isset ( $this->commentcrop )) ? $this->commentcrop : 180;
		
		$this->view->assign ( 'commentcrop', $charCrop );
		$this->view->assign ( 'comments', $this->commentsRepository->findByPost ( $post ) );
		$this->view->assign ( 'shareLink', $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'] );
		$this->view->assign ( 'advertUser', $currentPost->getUser ()->getAccounts () );
		$this->view->assign ( 'noImage', $noImage );
		$this->view->assign ( 'images', $images );
		$this->view->assign ( 'post', $currentPost );
		$this->view->assign ( 'userLike', $this->getUserLikeAction ( $post ) );
		$this->view->assign ( 'userDisLike', $this->getUserDisLikeAction ( $post ) );
		$this->view->assign ( 'categories', $this->categoryRepository->findAll () );
	}
	
	/**
	 *
	 * @param string $post        	
	 * @return void
	 */
	public function editAction($post) {
		$currentPost = $this->postRepository->findByIdentifier ( $post );
		$images = $this->imageRepository->findByPost ( $currentPost );
		
		if ($this->securityContext->getAccount () != NULL) {
			$this->view->assign ( 'currentUserInd', $this->getCurrentUser ()->getAccounts () );
		} else {
			$this->view->assign ( 'currentUserInd', '' );
		}
		
		if ($images->getFirst () != NULL) {
			$images = $images;
			$noImage = "False";
		} else {
			$images = NULL;
			$noImage = "True";
		}
		if ($currentPost->getLost () == 1) {
			$type = "lost";
		} else {
			$type = "found";
		}
		$this->view->assign ( 'type', $type );
		$this->view->assign ( 'firstName', $this->getCurrentUser ()->getName ()->getFirstName () );
		$this->view->assign ( 'lastName', $this->getCurrentUser ()->getName ()->getLastName () );
		$this->view->assign ( 'comments', $this->commentsRepository->findByPost ( $post ) );
		$this->view->assign ( 'shareLink', $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'] );
		$this->view->assign ( 'advertUser', $currentPost->getUser ()->getAccounts () );
		$this->view->assign ( 'noImage', $noImage );
		$this->view->assign ( 'images', $images );
		$this->view->assign ( 'post', $currentPost );
		$this->view->assign ( 'userLike', $this->getUserLikeAction ( $post ) );
		$this->view->assign ( 'userDisLike', $this->getUserDisLikeAction ( $post ) );
		$this->view->assign ( 'categories', $this->categoryRepository->findAll () );
	}
	
	/**
	 *
	 * @param string $post        	
	 */
	public function deleteAction($post) {
		$currentPost = $this->postRepository->findByIdentifier ( $post );
		
		$images = $this->imageRepository->findByPost ( $currentPost );
		$comments = $this->commentsRepository->findByPost ( $currentPost );
		$uploaded_dir = __DIR__ . "/../../../../../../Packages/Application/Incvisio.LostFound/Resources/Public/Images/uploads/";
		
		if ($currentPost->getLost () == 1) {
			$url_type = "lost";
		} elseif ($currentPost->getFound () == 1) {
			$url_type = "found";
		}
		foreach ( $images as $image ) {
			unlink ( $uploaded_dir . $image->getImgTitle () );
			$this->imageRepository->remove ( $image );
		}
		
		foreach ( $comments as $comment ) {
			$commentsLikes = $this->commentLikeRepository->findByLikedComment ( $comment );
			foreach ( $commentsLikes as $commentLike ) {
				$this->commentLikeRepository->remove ( $commentLike );
			}
			$this->commentsRepository->remove ( $comment );
		}
		$this->postRepository->remove ( $currentPost );
		$this->persistenceManager->persistAll ();
		
		$this->flashMessageContainer->addMessage ( new Message ( $this->translator->translateById ( 'advert.removed', array (), NULL, NULL, 'Main', 'Incvisio.LostFound' ) ) );
		$this->redirectToUri ( "/post?type=" . $url_type );
	}
	public function getUserLikeAction($post) {
		$currentPost = $this->postRepository->findByIdentifier ( $post );
		$user = $currentPost->getUser ();
		$userLikes = $user->getLikes ();
		return $userLikes;
	}
	public function getUserDisLikeAction($post) {
		$currentPost = $this->postRepository->findByIdentifier ( $post );
		$user = $currentPost->getUser ();
		$userDisLikes = $user->getDislike ();
		return $userDisLikes;
	}
	
	/**
	 *
	 * @return void
	 */
	public function updateAction() {
		$args = $this->request->getArguments ();
		
		//Check the city
		$cityNameArr =  explode(',', $args["post"]["city"]);
		$addedCity = $cityNameArr[0];
		$cityCnt = $this->postRepository->checkCity($addedCity);
		
		if ($cityCnt['number'] == 0) {
			$this->flashMessageContainer->addMessage ( new \TYPO3\Flow\Error\Error ( $this->translator->translateById ( 'advert.err.city', array (), NULL, NULL, 'Main', 'Incvisio.LostFound' ) ) );
			$this->redirect( 'show', 'Post', NULL, array (
				'post' => $args ['post'] ["__identity"] 
			));
		}
		
		$post = $this->postRepository->findByIdentifier ( $args ['post'] ["__identity"] );
		$post->setDescription ( $args ["post"] ["description"] );
		$post->setCity ( $args ["post"] ["city"] );
		$post->setTitle ( $args ["post"] ["title"] );
		$post->setUserContacts ( $args ["post"] ["userContacts"] );
		$post->setCategory ( $this->categoryRepository->findByIdentifier ( $args ["post"] ["category"] ) );
		$post->setPlace ( $args ["post"] ["place"] );
		
		$post->setDateLostOrFound ( new \TYPO3\Flow\Utility\Now () );
		
		$post->setActive ( 1 );
		$post->setPublishDate ( new \TYPO3\Flow\Utility\Now () );
		
		$this->postRepository->update ( $post );

		if (count($_FILES['file']['name']) > 0 && isset($_FILES['file']['name'])){
 
			$uploaded_dir = __DIR__ . "/../../../../../../Packages/Application/Incvisio.LostFound/Resources/Public/Images/uploads/";

			for ($i=0; $i < count($_FILES['file']['name']); $i++) {
				
				if ($_FILES['file']['name'][$i] != "") {
					$filename = $_FILES['file']['name'][$i];
					if (is_dir ( $uploaded_dir . $filename ) == false) {
						
						move_uploaded_file ( $_FILES['file']['tmp_name'][$i], $uploaded_dir . $filename );
						$setFileName = $_FILES['file']['name'][$i];
						//create a thumbnail
						$this->scaleImage($uploaded_dir . $filename, $uploaded_dir . 'thumb_'.$filename, $this->thumbSize);
						
					} else {
						$setFileName = $filename.time(); // rename the file if another one exist
						$new_dir = $uploaded_dir . $setFileName;
						rename( $_FILES['file']['tmp_name'][$i], $new_dir );
					}
					
					$newImage = new \Incvisio\LostFound\Domain\Model\Image ();
					$newImage->setImgTitle($setFileName );
					$newImage->setPost($post );
					$this->imageRepository->add($newImage );
				}
			}
		}
		
		$this->persistenceManager->persistAll();
		$this->redirect ( 'show', 'Post', NULL, array (
			'post' => $args ['post'] ["__identity"] 
		) );
	}
	
	/**
	 *
	 * @param string $image        	
	 * @return void
	 */
	public function deleteImageAction($image) {
		$args = $this->request->getArguments ();
		$getImage = $this->imageRepository->findByIdentifier($image);
		$uploaded_dir = __DIR__ . "/../../../../../../Packages/Application/Incvisio.LostFound/Resources/Public/Images/uploads/";
		
		@unlink($uploaded_dir . $getImage->getImgTitle());
		$this->imageRepository->remove ( $getImage );
		$this->persistenceManager->persistAll ();
		$this->redirect('edit', 'Post', NULL, array (
				'post' => $args ['post'] ["__identity"])
		);
	}
	/**
	 *
	 * @param string $post        	
	 * @return void
	 */
	public function setUserLikeAction($post) {
		if ($this->securityContext->getAccount () != NULL) {
			$this->getCurrentUser ();
			
			$currentPost = $this->postRepository->findByIdentifier ( $post );
			$user = $currentPost->getUser ();
			if ($this->getCurrentUser () == $user) {
				$this->flashMessageContainer->addMessage ( new \TYPO3\Flow\Error\Error ( $this->translator->translateById ( 'main.messages.yourAdd', array (), NULL, NULL, 'Main', 'Incvisio.LostFound' ) ) );
				$this->redirect ( 'show', 'Post', NULL, array (
						'post' => $post 
				) );
			}
			$findPrevLikes = $this->postLikeRepository->findByUserAndPost ( $this->getCurrentUser (), $post )->getFirst ();
			
			if ($findPrevLikes == NULL) {
				
				$postLikes = $currentPost->getLikes ();
				$currentPost->setLikes ( $postLikes + 1 );
				$this->postRepository->update ( $currentPost );
				
				$newPostLike = new PostLike ();
				$newPostLike->setUser ( $this->getCurrentUser () );
				$newPostLike->setLikedPost ( $post );
				$this->postLikeRepository->add ( $newPostLike );
				
				$this->persistenceManager->persistAll ();
				$this->redirect ( 'show', 'Post', NULL, array (
						'post' => $post 
				) );
			} else {
				
				$this->flashMessageContainer->addMessage ( new \TYPO3\Flow\Error\Error ( $this->translator->translateById ( 'main.messages.liked', array (), NULL, NULL, 'Main', 'Incvisio.LostFound' ) ) );
				$this->redirect ( 'show', 'Post', NULL, array (
						'post' => $post 
				) );
			}
		} else {
			
			$this->flashMessageContainer->addMessage ( new \TYPO3\Flow\Error\Error ( $this->translator->translateById ( 'main.messages.pleaseLogin', array (), NULL, NULL, 'Main', 'Incvisio.LostFound' ) ) );
			$this->redirect ( 'show', 'Post', NULL, array (
					'post' => $post 
			) );
		}
	}
	
	/**
	 *
	 * @param string $post        	
	 * @return void
	 */
	public function setUserDisLikeAction($post) {
		if ($this->securityContext->getAccount () != NULL) {
			
			$currentPost = $this->postRepository->findByIdentifier ( $post );
			$user = $currentPost->getUser ();
			
			if ($this->getCurrentUser () == $user) {
				$this->flashMessageContainer->addMessage ( new \TYPO3\Flow\Error\Error ( $this->translator->translateById ( 'main.messages.yourAdd', array (), NULL, NULL, 'Main', 'Incvisio.LostFound' ) ) );
				$this->redirect ( 'show', 'Post', NULL, array (
						'post' => $post 
				) );
			}
			
			$findPrevLikes = $this->postLikeRepository->findByUserAndPost ( $this->getCurrentUser (), $post )->getFirst ();
			if ($findPrevLikes == NULL) {
				$postDisLikes = $currentPost->getDislikes ();
				$currentPost->setDislikes ( $postDisLikes + 1 );
				$this->postRepository->update ( $currentPost );
				
				$newPostLike = new PostLike ();
				$newPostLike->setUser ( $this->getCurrentUser () );
				$newPostLike->setLikedPost ( $post );
				$this->postLikeRepository->add ( $newPostLike );
				$this->persistenceManager->persistAll ();
				$this->redirect ( 'show', 'Post', NULL, array (
						'post' => $post 
				) );
			} else {
				$this->flashMessageContainer->addMessage ( new \TYPO3\Flow\Error\Error ( $this->translator->translateById ( 'main.messages.liked', array (), NULL, NULL, 'Main', 'Incvisio.LostFound' ) ) );
				$this->redirect ( 'show', 'Post', NULL, array (
						'post' => $post 
				) );
			}
		} else {
			$this->flashMessageContainer->addMessage ( new \TYPO3\Flow\Error\Error ( $this->translator->translateById ( 'main.messages.pleaseLogin', array (), NULL, NULL, 'Main', 'Incvisio.LostFound' ) ) );
			$this->redirect ( 'show', 'Post', NULL, array (
					'post' => $post 
			) );
		}
	}
	/**
	 *
	 * @param string $post        	
	 * @return void
	 */
	public function markFoundAction($post) {
		$currentPost = $this->postRepository->findByIdentifier ( $post );
		$currentPost->setActive ( 0 );
		$this->postRepository->update ( $currentPost );
		$this->persistenceManager->persistAll ();
		$this->flashMessageContainer->addMessage ( new Message ( $this->translator->translateById ( 'advert.found', array (), NULL, NULL, 'Main', 'Incvisio.LostFound' ) ) );
		$this->redirect ( 'show', 'Post', NULL, array (
				'post' => $currentPost 
		) );
	}
	/**
	 *
	 * @param string $lost_input        	
	 * @param string $found_input        	
	 * @param string $city_input        	
	 * @param string $place_input        	
	 * @param string $date_from        	
	 * @param string $date_to        	
	 * @param string $category_found        	
	 * @param string $category_lost        	
	 * @param int $page        	
	 * @param string $type        	
	 * @return array
	 */
	public function getPostsAction($lost_input = NULL, $found_input = NULL, $city_input = NULL, $place_input = NULL, $date_from = NULL, $date_to = NULL, $category_found = NULL, $category_lost = NULL, $page = NULL, $type = NULL) {
		$item_per_page = 5;
		if (($page > 0) && isset ( $page )) {
			$page_number = $page;
		} else {
			$page_number = 1;
		}
		
		$page_position = (($page_number - 1) * $item_per_page);
		if ($type == 'lost') {
			$posts = $this->loadLost($lost_input, $city_input, $place_input, $date_from, $date_to, $category_lost, $page_position, $item_per_page );
			$count = $this->postRepository->countLost( $lost_input, $city_input, $category_lost );
		} elseif ($type == 'found') {
			$posts = $this->loadFound ( $found_input, $city_input, $place_input, $date_from, $date_to, $category_found, $page_position, $item_per_page );
			$count = $this->postRepository->countFound( $found_input, $city_input, $category_found );
		}
		
		$total_pages = ceil( $count['number'] / $item_per_page );
		$pagin = $this->paginate_function ( $item_per_page, $page_number, $count, $total_pages, $city_input, $lost_input, $found_input, $category_lost, $category_found );
		
		$this->view->assign ( 'pagin', $pagin );
		$this->view->assign ( 'count', $count );
		$this->view->assign ( 'posts', $posts );
	}
	
	/**
	 *
	 * @param string $lost_input        	
	 * @param string $found_input        	
	 * @return array
	 */
	public function getAdvSearchAction($lost_input = NULL, $found_input = NULL) {
		if (isset ( $lost_input )) {
			$posts = $this->loadLost ( $lost_input, NULL, NULL, NULL, NULL, NULL, 0, 10 );
		} elseif (isset ( $found_input )) {
			$posts = $this->loadFound ( $found_input, NULL, NULL, NULL, NULL, NULL, 0, 10 );
		}
		
		return json_encode ( $posts );
	}
	
	/**
	 *
	 * @param string $city        	
	 * @return array
	 */
	public function getCityAction($city) {
		$cities = $this->postRepository->foundCity ( $city );
		
		return json_encode ( $cities );
	}
	/**
	 *
	 * @param string $lost_input        	
	 * @param string $city_input        	
	 * @param string $place_input        	
	 * @param string $date_from        	
	 * @param string $date_to        	
	 * @param string $category_lost        	
	 * @param int $page_position        	
	 * @param int $item_per_page        	
	 * @return array.
	 */
	public function loadLost($lost_input, $city_input, $place_input, $date_from, $date_to, $category_lost, $page_position, $item_per_page) {
		
		$results = $this->postRepository->findWithFilterLost ( $lost_input, NULL, $city_input, $place_input, $date_from, $date_to, NULL, $category_lost, $page_position, $item_per_page );
		$newResults = array ();
		$uploaded_dir = __DIR__ . "/../../../../../../Packages/Application/Incvisio.LostFound/Resources/Public/Images/uploads/";
		
		if (count ( $results ) > 0) {
			
			foreach ( $results as $result ) {	
				$id = $result ['persistence_object_identifier'];
				$image = $this->imageRepository->findByPost ( $id )->getFirst ();
				$imageName = 'NULL';
				$thumb = 'NULL';
				
				if (!empty($image)) {
					$imageName = $image->getImgTitle();
					if (file_exists($uploaded_dir . 'thumb_' .$imageName)) {
						$thumb = 'thumb_' .$imageName;
					} else {
						if (file_exists($uploaded_dir . $imageName)) {
							if ($this->scaleImage($uploaded_dir . $imageName, $uploaded_dir . 'thumb_'.$imageName, $this->thumbSize))
								$thumb = 'thumb_' .$imageName;
						}
					}
				}
				
				$title = $result ['title'];
				// strip tags to avoid breaking any html
				$desc = strip_tags ( $result ['description'] );
				if (strlen ( $result ['description'] ) > 150) {
					// truncate string
					$stringCut = substr ( $result ['description'], 0, 150 );
					// make sure it ends in a word so assassinate doesn't become ass...
					$result ['description'] = substr ( $stringCut, 0, strrpos ( $stringCut, ' ' ) ) . '...';
				}
				
				$desc = $result ['description'];
				$active = $result ['active'];
				$timelostorfound = $result ['timelostorfound'];
				$datelostorfound = $result ['datelostorfound'];
				$city = $result ['city'];
				$place = $result ['place'];
				$newResults [] = array (
						"id" => $id,
						"title" => $title,
						"description" => $desc,
						"active" => $active,
						"image" => $imageName,
						"thumb" => $thumb,
						"place" => $place,
						"city" => $city,
						"timelostorfound" => $timelostorfound,
						"datelostorfound" => $datelostorfound 
				);
			}
		}
		
		return $newResults;
	}
	
	/**
	 *
	 * @param string $found_input        	
	 * @param string $city_input        	
	 * @param string $place_input        	
	 * @param string $date_from        	
	 * @param string $date_to        	
	 * @param string $category_founds        	
	 * @return array.
	 */
	public function loadFound($found_input, $city_input, $place_input, $date_from, $date_to, $category_founds, $page_position, $item_per_page) {
		
		$results = $this->postRepository->findWithFilterFound ( NULL, $found_input, $city_input, $place_input, $date_from, $date_to, $category_founds, NULL, $page_position, $item_per_page );
		$newResults = array ();
		$uploaded_dir = __DIR__ . "/../../../../../../Packages/Application/Incvisio.LostFound/Resources/Public/Images/uploads/";
		
		if (count ( $results ) > 0) {
			foreach ( $results as $result ) {
				
				$id = $result ['persistence_object_identifier'];
				$image = $this->imageRepository->findByPost ( $id )->getFirst ();
				$imageName = 'NULL';
				$thumb = 'NULL';
				
				if (!empty($image)) {
					$imageName = $image->getImgTitle();
					if (file_exists($uploaded_dir . 'thumb_' .$imageName)) {
						$thumb = 'thumb_' .$imageName;
					} else {
						if (file_exists($uploaded_dir . $imageName)) {
							if ($this->scaleImage($uploaded_dir . $imageName, $uploaded_dir . 'thumb_'.$imageName, $this->thumbSize))
								$thumb = 'thumb_' .$imageName;
						}
					}
				}
				
				$title = $result ['title'];
				$active = $result ['active'];
				$desc = strip_tags ( $result ['description'] );
				
				if (strlen ( $result ['description'] ) > 150) {
					// truncate string
					$stringCut = substr ( $result ['description'], 0, 150 );
					// make sure it ends in a word so assassinate doesn't become ass...
					$result ['description'] = substr ( $stringCut, 0, strrpos ( $stringCut, ' ' ) ) . '...';
				}
				
				$desc = $result ['description'];
				$timelostorfound = $result ['timelostorfound'];
				$datelostorfound = $result ['datelostorfound'];
				$city = $result ['city'];
				$place = $result ['place'];
				$newResults [] = array (
						"id" => $id,
						"title" => $title,
						"active" => $active,
						"description" => $desc,
						"image" => $imageName,
						"thumb" => $thumb,
						"place" => $place,
						"city" => $city,
						"timelostfound" => $timelostorfound,
						"datelostorfound" => $datelostorfound 
				);
			}
		}
		return $newResults;
	}
	
	/**
	 *
	 * @param string $type        	
	 * @return void
	 */
	public function newAction($type) {
		if ($type == "lost") {
			$this->view->assign ( 'lost', '1' );
			$this->view->assign ( 'found', '0' );
		} else {
			$this->view->assign ( 'lost', '0' );
			$this->view->assign ( 'found', '1' );
		}
		$this->view->assign ( 'type', $type );
		$this->view->assign ( 'avatar', $this->getCurrentUser ()->getSocialPhoto () );
		$this->view->assign ( 'firstName', $this->getCurrentUser ()->getName ()->getFirstName () );
		$this->view->assign ( 'lastName', $this->getCurrentUser ()->getName ()->getLastName () );
		$this->view->assign ( 'categories', $this->categoryRepository->findAll () );
	}
	
	/**
	 *
	 * @return void
	 */
	public function createAction() {
		
		$args = $this->request->getArguments ();
		if ($args["newPost"]["lost"] == 1) {
			$url_type = "lost";
		} elseif ($args["newPost"]["found"] == 1) {
			$url_type = "found";
		}
		
		//Check the city
		$cityNameArr =  explode(',', $args["newPost"]["city"]);
		$addedCity = $cityNameArr[0];
		$cityCnt = $this->postRepository->checkCity($addedCity);
		
		if ($cityCnt['number'] == 0) {
			$this->flashMessageContainer->addMessage ( new \TYPO3\Flow\Error\Error( $this->translator->translateById ( 'advert.err.city', array (), NULL, NULL, 'Main', 'Incvisio.LostFound' ) ) );
			$this->redirectToUri("/post/new/?type=" . $url_type );
		}
				
		$newPost = new Post ();
		$newPost->setDescription ( $args ["newPost"] ["description"] );
		$newPost->setLost ( $args ["newPost"] ["lost"] );
		$newPost->setFound ( $args ["newPost"] ["found"] );
		$newPost->setCity ( $args ["newPost"] ["city"] );
		$newPost->setTitle ( $args ["newPost"] ["title"] );
		$newPost->setUserContacts ( $args ["newPost"] ["userContacts"] );
		$newPost->setCategory ( $this->categoryRepository->findByIdentifier ( $args ["newPost"] ["category"] ) );
		$newPost->setPlace ( $args ["newPost"] ["place"] );
		$newPost->setDateLostOrFound ( new \TYPO3\Flow\Utility\Now () );
		$newPost->setUser ( $this->getCurrentUser () );
		$newPost->setActive ( 1 );
		$newPost->setPublishDate ( new \TYPO3\Flow\Utility\Now () );
		$newPost->setLikes ( 0 );
		$newPost->setDislikes ( 0 );
		
		$this->postRepository->add ( $newPost );
		
		if (isset ( $_FILES ) && ! empty ( $_FILES )) {
			
			$count = 0;
			$uploaded_dir = __DIR__ . "/../../../../../../Packages/Application/Incvisio.LostFound/Resources/Public/Images/uploads/";
			
			foreach ( $_FILES as $file ) {
				
				foreach ( $file ['name'] as $filename ) {
					
					if ($file ['name'] [$count] != "") {
						$file_tmp = $file ['tmp_name'] [$count];
						if (is_dir ( $uploaded_dir . $filename ) == false) {
							move_uploaded_file ( $file_tmp, $uploaded_dir . $filename );
							//create a thumbnail
							$this->scaleImage($uploaded_dir . $filename, $uploaded_dir . 'thumb_'.$filename, $this->thumbSize);
							$setFileName = $filename;
						} else {
							$setFileName = $filename . time(); // rename the file if another one exist
							$new_dir = $uploaded_dir . $setFileName;
							rename ( $file_tmp, $new_dir );
						}
						$newImage = new \Incvisio\LostFound\Domain\Model\Image ();
						$newImage->setImgTitle ( $setFileName );
						$newImage->setPost ( $newPost );
						$this->imageRepository->add ( $newImage );
						$count ++;
					}
				}
			}
		}
		
		$this->persistenceManager->persistAll ();
		$this->flashMessageContainer->addMessage ( new Message ( $this->translator->translateById ( 'advert.add', array (), NULL, NULL, 'Main', 'Incvisio.LostFound' ) ) );
		
		$this->redirectToUri ( "/post?type=" . $url_type );
	}
	
	/**
	 *
	 * @return array
	 */
	public function changeAvatarAction() {
		$user = $this->getCurrentUser ();
		// ########### Configuration ##############
		$thumb_square_size = 200; // Thumbnails will be cropped to 200x200 pixels
		$max_image_size = 500; // Maximum image size (height and width)
		$thumb_prefix = "avatar_"; // Normal thumb Prefix
		$destination_folder = __DIR__ . "/../../../../../../Packages/Application/Incvisio.LostFound/Resources/Public/Images/uploads/user_avatar/"; // upload directory ends with / (slash)
		$jpeg_quality = 90; // jpeg quality
		                    // #########################################
		                    
		// continue only if $_POST is set and it is a Ajax request
		if (isset ( $_POST ) && isset ( $_SERVER ['HTTP_X_REQUESTED_WITH'] ) && strtolower ( $_SERVER ['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest') {
			
			// check $_FILES['ImageFile'] not empty
			if (! isset ( $_FILES ['image_file'] ) || ! is_uploaded_file ( $_FILES ['image_file'] ['tmp_name'] )) {
				die ( 'Image file is Missing!' ); // output error when above checks fail.
			}
			
			// get uploaded file info before we proceed
			$image_name = $_FILES ['image_file'] ['name']; // file name
			$image_size = $_FILES ['image_file'] ['size']; // file size
			$image_temp = $_FILES ['image_file'] ['tmp_name']; // file temp
			
			$image_size_info = getimagesize ( $image_temp ); // gets image size info from valid image file
			
			if ($image_size_info) {
				$image_width = $image_size_info [0]; // image width
				$image_height = $image_size_info [1]; // image height
				$image_type = $image_size_info ['mime']; // image type
			} else {
				die ( "Make sure image file is valid!" );
			}
			
			// switch statement below checks allowed image type
			// as well as creates new image from given file
			switch ($image_type) {
				case 'image/png' :
					$image_res = imagecreatefrompng ( $image_temp );
					break;
				case 'image/gif' :
					$image_res = imagecreatefromgif ( $image_temp );
					break;
				case 'image/jpeg' :
				case 'image/pjpeg' :
					$image_res = imagecreatefromjpeg ( $image_temp );
					break;
				default :
					$image_res = false;
			}
			
			if ($image_res) {
				// Get file extension and name to construct new file name
				$image_info = pathinfo ( $image_name );
				$image_extension = strtolower ( $image_info ["extension"] ); // image extension
				$image_name_only = strtolower ( $image_info ["filename"] ); // file name only, no extension
				                                                        
				// create a random name for new image (Eg: fileName_293749.jpg) ;
				$new_file_name = $image_name_only . '_' . rand ( 0, 9999999999 ) . '.' . $image_extension;
				
				// folder path to save resized images and thumbnails
				$thumb_save_folder = $destination_folder . $thumb_prefix . $new_file_name;
				$image_save_folder = $destination_folder . $new_file_name;
				
				// call normal_resize_image() function to proportionally resize image
				if ($this->normal_resize_image ( $image_res, $image_save_folder, $image_type, $max_image_size, $image_width, $image_height, $jpeg_quality )) {
					// call crop_image_square() function to create square thumbnails
					if (! $this->crop_image_square ( $image_res, $thumb_save_folder, $image_type, $thumb_square_size, $image_width, $image_height, $jpeg_quality )) {
						die ( 'Error Creating thumbnail' );
					}
					$saveImageDest = 'http://' . $_SERVER ['HTTP_HOST'] . '/_Resources/Static/Packages/Incvisio.LostFound/Images/uploads/user_avatar/';
					$user->setSocialPhoto ( $saveImageDest . $thumb_prefix . $new_file_name );
					$this->userRepository->update ( $user );
					$this->persistenceManager->persistAll ();
				}
				imagedestroy ( $image_res ); // freeup memory
				return "Аватар змінено";
			}
		}
	}
	
	/**
	 * 
	 * @param string $source
	 * @param string $destination
	 * @param number $maxSize
	 */
	protected function scaleImage($source, $destination, $maxSize) {
		
		list($width, $height, $type, $attr) = getimagesize($source);
		$imageScale = min ($maxSize / $width, $maxSize / $height );
		$newWidth = ceil($imageScale * $width);
		$newHeight = ceil($imageScale * $height);

		switch ($type) {
			case 1:
				$resOrg = imagecreatefromgif($source);
				break;
			case 3:
				$resOrg = imagecreatefrompng($source);
				break;
			default:
				$resOrg = imagecreatefromjpeg($source);
				break;
		}
		if (isset($resOrg)){
			if (($resScaled = imagescale($resOrg, $newWidth, $newHeight)) !== FALSE) {
				switch ($type) {
					case 1:
						imagegif($resScaled, $destination);
						break;
					case 3:
						imagepng($resScaled, $destination);
						break;
					default:
						imagejpeg($resScaled, $destination);
				}
				imagedestroy($resScaled);
			}
			
			imagedestroy($resOrg);
			
			return true;
		} else {
			return false;
		}
	}
	
	public function normal_resize_image($source, $destination, $image_type, $max_size, $image_width, $image_height, $quality) {
		if ($image_width <= 0 || $image_height <= 0) {
			return false;
		} // return false if nothing to resize
		                                                           
		// do not resize if image is smaller than max size
		if ($image_width <= $max_size && $image_height <= $max_size) {
			if ($this->save_image ( $source, $destination, $image_type, $quality )) {
				return true;
			}
		}
		
		// Construct a proportional size of new image
		$image_scale = min ( $max_size / $image_width, $max_size / $image_height );
		$new_width = ceil ( $image_scale * $image_width );
		$new_height = ceil ( $image_scale * $image_height );
		
		$new_canvas = imagecreatetruecolor ( $new_width, $new_height ); // Create a new true color image
		$image = imagecreatefromjpeg($source);
		// Copy and resize part of an image with resampling
		if (imagecopyresampled ( $new_canvas, $image, 0, 0, 0, 0, $new_width, $new_height, $image_width, $image_height )) {
			$this->save_image ( $new_canvas, $destination, $image_type, $quality ); // save resized image
		}
		
		return true;
	}
	public function crop_image_square($source, $destination, $image_type, $square_size, $image_width, $image_height, $quality) {
		if ($image_width <= 0 || $image_height <= 0) {
			return false;
		} // return false if nothing to resize
		
		if ($image_width > $image_height) {
			$y_offset = 0;
			$x_offset = ($image_width - $image_height) / 2;
			$s_size = $image_width - ($x_offset * 2);
		} else {
			$x_offset = 0;
			$y_offset = ($image_height - $image_width) / 2;
			$s_size = $image_height - ($y_offset * 2);
		}
		$new_canvas = imagecreatetruecolor ( $square_size, $square_size ); // Create a new true color image
		                                                                 
		// Copy and resize part of an image with resampling
		if (imagecopyresampled ( $new_canvas, $source, 0, 0, $x_offset, $y_offset, $square_size, $square_size, $s_size, $s_size )) {
			$this->save_image ( $new_canvas, $destination, $image_type, $quality );
		}
		
		return true;
	}
	
	// #### Saves image resource to file #####
	public function save_image($source, $destination, $image_type, $quality) {
		switch (strtolower ( $image_type )) { // determine mime type
			case 'image/png' :
				imagepng ( $source, $destination );
				return true; // save png file
				break;
			case 'image/gif' :
				imagegif ( $source, $destination );
				return true; // save gif file
				break;
			case 'image/jpeg' :
			case 'image/pjpeg' :
				imagejpeg ( $source, $destination, $quality );
				return true; // save jpeg file
				break;
			default :
				return false;
		}
	}
	
	/**
	 *
	 * @return array
	 */
	public function getAvatarAction() {
		$user = $this->getCurrentUser ();
		$avatar = $user->getSocialPhoto ();
		$avatarArray = array (
				"avatar" => $avatar 
		);
		return json_encode ( $avatarArray );
	}
	
	/**
	 *
	 * @return void
	 */
	public function createCommentAction() {
		$args = $this->request->getArguments ();
		$newComment = new Comments ();
		$newComment->setUser ( $this->getCurrentUser () );
		$post = $this->postRepository->findByIdentifier ( $args ["newComment"] ["post"] ["__identity"] );
		$newComment->setPost ( $post );
		$newComment->setLikes ( 0 );
		$newComment->setDislikes ( 0 );
		$newComment->setMessage ( $args ['commentsMessage'] );
		$newComment->setPublishDate ( new \DateTime () );
		$this->commentsRepository->add ( $newComment );
		$this->persistenceManager->persistAll ();
		$this->flashMessageContainer->addMessage ( new Message ( $this->translator->translateById ( 'main.messages.commentsAdd', array (), NULL, NULL, 'Main', 'Incvisio.LostFound' ) ) );
		$this->redirect ( 'show', 'Post', NULL, array (
				'post' => $post 
		) );
	}
	
	/**
	 *
	 * @param string $comment        	
	 * @return void
	 */
	public function setCommentLikeAction($comment) {
		$currentComment = $this->commentsRepository->findByIdentifier ( $comment );
		if ($this->securityContext->getAccount () != NULL) {
			
			$user = $currentComment->getUser ();
			if ($this->getCurrentUser () == $user) {
				$this->flashMessageContainer->addMessage ( new \TYPO3\Flow\Error\Error ( $this->translator->translateById ( 'main.messages.yourComment', array (), NULL, NULL, 'Main', 'Incvisio.LostFound' ) ) );
				$this->redirect ( 'show', 'Post', NULL, array (
						'post' => $currentComment->getPost () 
				) );
			}
			$findPrevLikes = $this->commentLikeRepository->findByUserAndUserAdvert ( $this->getCurrentUser (), $this->persistenceManager->getIdentifierByObject ( $currentComment ) )->getFirst ();
			if ($findPrevLikes == NULL) {
				$commentLikes = $currentComment->getLikes ();
				$currentComment->setLikes ( $commentLikes + 1 );
				$this->commentsRepository->update ( $currentComment );
				$newCommentLike = new CommentLike ();
				$newCommentLike->setUser ( $this->getCurrentUser () );
				$newCommentLike->setLikedComment ( $this->persistenceManager->getIdentifierByObject ( $currentComment ) );
				$this->commentLikeRepository->add ( $newCommentLike );
				$this->persistenceManager->persistAll ();
				$this->redirect ( 'show', 'Post', NULL, array (
						'post' => $currentComment->getPost () 
				) );
			} else {
				$this->flashMessageContainer->addMessage ( new \TYPO3\Flow\Error\Error ( $this->translator->translateById ( 'main.messages.liked', array (), NULL, NULL, 'Main', 'Incvisio.LostFound' ) ) );
				$this->redirect ( 'show', 'Post', NULL, array (
						'post' => $currentComment->getPost () 
				) );
			}
		} else {
			$this->flashMessageContainer->addMessage ( new \TYPO3\Flow\Error\Error ( $this->translator->translateById ( 'main.messages.pleaseLogin', array (), NULL, NULL, 'Main', 'Incvisio.LostFound' ) ) );
			$this->redirect ( 'show', 'Post', NULL, array (
					'post' => $currentComment->getPost () 
			) );
		}
	}
	
	/**
	 *
	 * @param string $comment        	
	 * @return void
	 */
	public function setCommentDisLikeAction($comment) {
		$currentComment = $this->commentsRepository->findByIdentifier ( $comment );
		if ($this->securityContext->getAccount () != NULL) {
			
			$user = $currentComment->getUser ();
			if ($this->getCurrentUser () == $user) {
				$this->flashMessageContainer->addMessage ( new \TYPO3\Flow\Error\Error ( $this->translator->translateById ( 'main.messages.yourComment', array (), NULL, NULL, 'Main', 'Incvisio.LostFound' ) ) );
				$this->redirect ( 'show', 'Post', NULL, array (
						'post' => $currentComment->getPost () 
				) );
			}
			$findPrevLikes = $this->commentLikeRepository->findByUserAndUserAdvert ( $this->getCurrentUser (), $this->persistenceManager->getIdentifierByObject ( $currentComment ) )->getFirst ();
			if ($findPrevLikes == NULL) {
				$commentDisLikes = $currentComment->getDislikes ();
				$currentComment->setDislikes ( $commentDisLikes + 1 );
				$this->commentsRepository->update ( $currentComment );
				$newCommentLike = new CommentLike ();
				$newCommentLike->setUser ( $this->getCurrentUser () );
				$newCommentLike->setLikedComment ( $this->persistenceManager->getIdentifierByObject ( $currentComment ) );
				$this->commentLikeRepository->add ( $newCommentLike );
				$this->persistenceManager->persistAll ();
				$this->redirect ( 'show', 'Post', NULL, array (
						'post' => $currentComment->getPost () 
				) );
			} else {
				$this->flashMessageContainer->addMessage ( new \TYPO3\Flow\Error\Error ( $this->translator->translateById ( 'main.messages.liked', array (), NULL, NULL, 'Main', 'Incvisio.LostFound' ) ) );
				$this->redirect ( 'show', 'Post', NULL, array (
						'post' => $currentComment->getPost () 
				) );
			}
		} else {
			$this->flashMessageContainer->addMessage ( new \TYPO3\Flow\Error\Error ( $this->translator->translateById ( 'main.messages.pleaseLogin', array (), NULL, NULL, 'Main', 'Incvisio.LostFound' ) ) );
			$this->redirect ( 'show', 'Post', NULL, array (
					'post' => $currentComment->getPost () 
			) );
		}
	}
	public function paginate_function($item_per_page, $current_page, $total_records, $total_pages, $city_input = NULL, $lost_input = NULL, $found_input = NULL, $category_lost = NULL, $category_found = NULL) {
		$pagination = '';
		
		if ($total_pages > 0 && $total_pages != 1 && $current_page <= $total_pages) {
			// verify total pages and current page number
			
			$blockSize = 6;
			if ($current_page <= $blockSize) {
				$currentBlockStart = 1;
			} else {
				$q = floor ( $current_page / $blockSize );
				$currentBlockStart = ($q * $blockSize) + 1;
			}
			
			$currentBlockEnd = $currentBlockStart + $blockSize;
			
			if ($currentBlockEnd > $total_pages) {
				$currentBlockEnd = $total_pages + 1;
			}
			
			$pagination .= '<ul class="pagination newpad">';
			
			// $right_links = $current_page + 3;
			$previous = $current_page - 1; // previous link
			$next = $current_page + 1; // next link
			$first_link = true; // boolean var to decide our first link
			$next_link = ($next < $total_pages) ? $next : $total_pages;
			
			if ($current_page > 1) {
				
				$previous_link = ($previous == 0) ? 1 : $previous;
				$pagination .= '<li id="pagination_list" class="leftText waves-effect wawess"><a data-page="1" data-city="' . $city_input . '" data-lost="' . $lost_input . '" data-found="' . $found_input . '" data-fcat="' . $category_found . '" data-lcat="' . $category_lost . '" title="First"><i class="fa fa-angle-double-left"></i></a></li>'; // first link
				$pagination .= '<li id="pagination_list" class="leftText waves-effect wawess"><a data-page="' . $previous_link . '" data-city="' . $city_input . '" data-lost="' . $lost_input . '" data-found="' . $found_input . '" data-fcat="' . $category_found . '" data-lcat="' . $category_lost . '" title="Previous"><i class="fa fa-angle-left"></i></a></li>'; // previous link
				$first_link = false; // set first link to false
			} else {
				$pagination .= ' <li id="pagination_list" class="leftText disabled wawess"><i class="fa fa-angle-double-left"></i></li>'; // first link
				$pagination .= '<li id="pagination_list" class="leftText disabled wawess"><i class="fa fa-angle-left"></i></li>'; // previous link
			}
			
			for($i = $currentBlockStart; $i < $currentBlockEnd; $i ++) { // create right-hand side links
				if ($i == $current_page) {
					$pagination .= '<li id="pagination_list" class="active wawessac">' . $current_page . '</li>';
				} else {
					$pagination .= '<li id="pagination_list" class="waves-effect wawess"><a data-page="' . $i . '" data-city="' . $city_input . '" data-lost="' . $lost_input . '" data-found="' . $found_input . '" data-fcat="' . $category_found . '" data-lcat="' . $category_lost . '" title="Page ' . $i . '">' . $i . '</a></li>';
				}
			}
			
			if ($current_page < $total_pages) {
				$pagination .= '<li id="pagination_list" class="waves-effect wawess rightText"><a data-page="' . $total_pages . '" data-city="' . $city_input . '" data-lost="' . $lost_input . '" data-found="' . $found_input . '" data-fcat="' . $category_found . '" data-lcat="' . $category_lost . '" title="Last"><i class="fa fa-angle-double-right"></i></a></li>'; // next link
				$pagination .= '<li  id="pagination_list" class="waves-effect wawess rightText"><a data-page="' . $next_link . '" data-city="' . $city_input . '" data-lost="' . $lost_input . '" data-found="' . $found_input . '" data-fcat="' . $category_found . '" data-lcat="' . $category_lost . '" title="Next"><i class="fa fa-angle-right"></i></a></li>'; // last link
			} else {
				$pagination .= '<li id="pagination_list" class="disabled wawess rightText"><i class="fa fa-angle-double-right"></i></li>'; // next link
				$pagination .= '<li  id="pagination_list" class="disabled wawess rightText"><i class="fa fa-angle-right"></i></li>'; // last link
			}
			
			$pagination .= '</ul>';
		}
		return $pagination; // return pagination links
	}
}