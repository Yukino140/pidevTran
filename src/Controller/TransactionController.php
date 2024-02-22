<?php

namespace App\Controller;

use App\Entity\Facture;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use App\Repository\CompteRepository;
use App\Repository\FactureRepository;
use App\Repository\TransactionRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TransactionController extends AbstractController
{
    #[Route('/transaction', name: 'app_transaction')]
    public function index(): Response
    {
        return $this->render('clientdash/client/client.html.twig', [
            'controller_name' => 'TransactionController',
        ]);
    }
    #[Route('/getAllTransactions',name:'getAllT')]
    public function getAll(TransactionRepository $repo,CompteRepository $rep):Response
    {
        $client=$rep->find(1);

        $res =$repo->findAll();
        return $this->render('clientdash/transaction/TransactionsHistory.html.twig',[
            'transac'=> $res,
        ]);
    }

    #[Route('/deposit', name:'depo')]
    public function deposit(Request $req,ManagerRegistry $mg,CompteRepository $rep):Response
    {
        $em = $mg->getManager();
        $transaction=new Transaction();

        $client=$rep->find(1);
        $transaction->setIdCompte($client);
        $transaction->setTypeTransaction(TransactionType::Versement);
        $transaction->setDate(new \DateTime('now'));
        $form=$this->createForm(\App\Form\TransactionType::class,$transaction);
        $form->handleRequest($req);
        if($form->isSubmitted() && $form->isValid()){
            $client->setSolde($client->getSolde()+$transaction->getMontant());
            $em->persist($client);
            $em->persist($transaction);
            $em->flush();
            return $this->redirectToRoute('depo');
        }
        return $this->render('clientdash/transaction/deposit.html.twig',['form'=>$form->createView()]);

    }
    #[Route('/transfer',name:'trans')]
    public function transfers(Request $req, ManagerRegistry $mg,CompteRepository $rep):Response
    {
        $em = $mg->getManager();
        $transaction=new Transaction();
        $client=$rep->find(1);
        $transaction->setIdCompte($client);
        $transaction->setTypeTransaction(TransactionType::Virement);
        $transaction->setDate(new \DateTime('now'));
        $form=$this->createForm(\App\Form\TransactionType::class,$transaction);
        $form->handleRequest($req);
        if($form->isSubmitted() && $form->isValid()){
            $em->persist($transaction);
            $em->flush();
            return $this->redirectToRoute('trans');
        }
        return $this->render('clientdash/transaction/transfert.html.twig',['form'=>$form->createView()]);
    }
    #[Route('/withdrawl' ,name:'retrait')]
    public function withdrawal(Request $req, ManagerRegistry $mg,CompteRepository $rep):Response
    {
        $em = $mg->getManager();
        $transaction=new Transaction();
        $client=$rep->find(1);
        $transaction->setIdCompte($client);
        $transaction->setTypeTransaction(TransactionType::Retrait);
        $transaction->setDate(new \DateTime('now'));
        $form=$this->createForm(\App\Form\TransactionType::class,$transaction);
        $form->handleRequest($req);
        if($form->isSubmitted() && $form->isValid()){
            if($client->getSolde()>$transaction->getMontant()){
            $client->setSolde($client->getSolde()-$transaction->getMontant());
            $em->persist($client);
            $em->persist($transaction);
            $em->flush();
                return $this->redirectToRoute('retrait');
            }else{
                return $this->redirectToRoute('retrait',['message'=>"You don't have that amount of money to withdraw"]);
            }

        }
        return $this->render('clientdash/transaction/withdrawl.html.twig',['form'=>$form->createView(),'cl'=>$client]);
    }


    #[Route('/getAllTransactionsForStaff',name:'getAll')]
    public function getAllTransactions(TransactionRepository $rep):Response
    {
        $res=$rep->findAll();
        return $this->render('staffdash/transactions/getAllTransactions.html.twig',[
         'res'=>$res
        ]);
    }
   /* #[Route('/showDetails',name:'detail')]
    public function details(TransactionRepository $rep):Response
    {

        $res=$rep->find(15);
        return $this->
    }*/

    #[Route('addFacture/{id}',name:'addF')]
    public function addFacture(Request $req, ManagerRegistry $mg,TransactionRepository $rep,$id):Response
    {
        $em = $mg->getManager();
        $facture=new Facture();
        $transaction=$rep->find($id);
        $facture->setIdTransaction($transaction);
        if(($transaction->getTypeTransaction()==TransactionType::Versement)and ($transaction->getTypeTransaction()==TransactionType::Virement)){
            $facture->setTax(1);
            $facture->setMontantTTC($transaction->getMontant()-($transaction->getMontant()*0.01));


        }else{
            $facture->setTax(0);
            $facture->setMontantTTC($transaction->getMontant());
        }
        $em->persist($facture);
        $em->flush();
        return $this->redirectToRoute('getAllT',[
            "tran"=>$transaction,
            "fac"=>$facture
        ]);

    }
    #[Route('showFact/{id}',name:'showF')]
    public function showFa($id,FactureRepository $repo,TransactionRepository $rep,CompteRepository $re):Response
    {
            $fact=$repo->findByIDTransaction($id);
            $tranc=$rep->find($id);
            $compte=$re->find($tranc->getIdCompte());
            return $this->render('clientdash/transaction/facture.html.twig',['tranc'=>$tranc,'fact'=>$fact,'compte'=>$compte]);
    }
    #[Route('deleteT/{id}',name:'deleteT')]
    public function deleteT($id,TransactionRepository $repo,FactureRepository $rep,ManagerRegistry $mg):Response
    {
        $facture=$rep->findByIDTransaction($id);
        $transaction=$repo->find($id);
        $em=$mg->getManager();
        $em->remove($facture);
        $em->flush();
        $em->remove($transaction);
        $em->flush();
        return $this->redirectToRoute('getAllT');

    }
    #[Route('deleteF/{id}',name:'deleteF')]
    public function deleteF($id,FactureRepository $rep,ManagerRegistry $mg):Response
    {
        $facture=$rep->find($id);
        $em=$mg->getManager();
        $em->remove($facture);
        $em->flush();
        return $this->redirectToRoute('getAllT');
    }

}
