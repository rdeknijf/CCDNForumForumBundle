<?php

namespace CCDNForum\ForumBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * DraftRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DraftRepository extends EntityRepository
{

    /**
     *
     * @access public
     */
    public function getTableIntegrityStatus()
    {
        $queryOrphanedDraftCount = $this->getEntityManager()
            ->createQuery('
                SELECT COUNT(DISTINCT d.id) AS orphanedDraftCount
                FROM CCDNForumForumBundle:Draft d
                WHERE d.createdBy IS NULL
            ');

        try {
            $result['orphanedDraftCount'] = $queryOrphanedDraftCount->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $result['orphanedDraftCount'] = '?';
        }

        return $result;
    }

    /**
     *
     * @access public
     * @param  int        $userId
     * @return Collection
     */
    public function findDraftsPaginated($userId)
    {

        $draftsQuery = $this->getEntityManager()
            ->createQuery('
                SELECT d
                FROM CCDNForumForumBundle:Draft d
                WHERE d.createdBy = :id
                ORDER BY d.createdDate ASC
                ')
            ->setParameter('id', $userId);

        try {
            return new Pagerfanta(new DoctrineORMAdapter($draftsQuery));
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    /**
     *
     * @access public
     * @param  Int        $draftId, Int $userId
     * @return null|Draft
     */
    public function findOneByIdForUserById($draftId, $userId)
    {

        $query = $this->getEntityManager()
            ->createQuery('
                SELECT d
                FROM CCDNForumForumBundle:Draft d
                WHERE d.id = :draftId AND d.createdBy = :userId')
            ->setParameters(array('draftId' => $draftId, 'userId' => $userId));

        try {
            return $query->getsingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

}
