<?php
namespace Incvisio\LostFound\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Incvisio.LostFound".    *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 */
class PostLikeRepository extends Repository {

	public function findByUserAndPost($user,$post){
		$query = $this->createQuery();
		$query->matching(
				$query->logicalAnd(
						$query->equals('user', $user),
						$query->equals('likedPost', $post)
				)
		);
		return $query->execute();
	}
}