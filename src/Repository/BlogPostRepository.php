<?php

namespace App\Repository;

use App\Entity\BlogPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BlogPost>
 */
class BlogPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogPost::class);
    }

    public function findBlogPostsPaginated(?int $page = 1, ?int $nbPerPage = 10): array
    {
        $entityManager = $this->getEntityManager();
        $dql = "SELECT b FROM App\Entity\BlogPost b";
        $query = $entityManager->createQuery($dql)
            ->setFirstResult(($page - 1) * $nbPerPage)
            ->setMaxResults($nbPerPage);
        $paginator = new Paginator($query);
        $results = $paginator->getQuery()->getResult();

        return [
            "total_items" => $paginator->count(),
            "data" => $results,
            "current_page" => $page,
            "pages" => ceil($paginator->count()/$nbPerPage),
            "has_previous_page" => $page - 1 != 0,
            "has_next_page" => !($page == ceil($paginator->count() / $nbPerPage)),
            "items_per_page" => $nbPerPage,
        ];
    }
}
