<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 *
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * @return Transaction[] Returns an array of Transaction objects
     */
    public function findListById($id=null): array
    {
        $req=$this->createQueryBuilder('t');
            if($id != null) {

                $req->andWhere('t.idCompte = :val')
                    ->setParameter('val', $id);
                    }

            return $req->getQuery()->getResult();
    }

    public function findListByDate($id,$date1=null,$date2=null): array
    {
        $query = $this->createQueryBuilder('t')
            ->andWhere('t.idCompte = :val')
            ->setParameter('val', $id);
            if($date1 != null){
                $query->andWhere('t.date > :date1')
                    ->setParameter('date1', $date1);
            }
            if(($date2 !=null) ){
                if(($date1==null)or($date2>$date1)){
                $query->andWhere('t.date< :date2')
                    ->setParameter('date2',$date2);
                }

            }



        return $query->getQuery()->getResult();
    }
    public function sumTransaction($id,$date1=null,$date2=null): float
    {
        $query = $this->createQueryBuilder('t')
            ->andWhere('t.idCompte = :val')
            ->setParameter('val', $id);
        if($date1 != null){
            $query->andWhere('t.date > :date1')
                ->setParameter('date1', $date1);
        }
        if(($date2 !=null) ){
            if(($date1==null)or($date2>$date1)){
                $query->andWhere('t.date< :date2')
                    ->setParameter('date2',$date2);
            }

        }
        $query->select('SUM(t.montant) AS total');



        return $query->getQuery()->getSingleScalarResult();
    }


}
