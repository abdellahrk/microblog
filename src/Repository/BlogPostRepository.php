<?php

namespace App\Repository;

use App\Entity\BlogPost;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Rami\EntityKitBundle\Repository\Traits\EntityPaginationTrait;

/**
 * @extends ServiceEntityRepository<BlogPost>
 */
class BlogPostRepository extends ServiceEntityRepository
{
    use EntityPaginationTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogPost::class);
    }

    public function findBlogPostsPaginated(?int $page = 1, ?int $nbPerPage = 10): array
    {
        $query = $this
            ->createQueryBuilder('b')
            ->getQuery();
        return $this->paginateResult($query, $page, $nbPerPage);

    }

    public function getBlogPostsByAuthor(User $author, ?int $page =1, ?int $nbPerPage=20): array
    {
        $query = $this->createQueryBuilder('b')
            ->where('b.author = :author')
            ->setParameter('author', $author)
            ->getQuery();

        return $this->paginateResult($query, $page, $nbPerPage);
    }
}
