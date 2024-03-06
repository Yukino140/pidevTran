<?php

namespace App\Controller;

use App\Entity\Facture;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use App\Repository\CompteRepository;
use App\Repository\FactureRepository;
use App\Repository\TransactionRepository;
use Couchbase\ViewResult;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Snappy\Pdf;
use PharIo\Manifest\Email;
use phpDocumentor\Reflection\Types\False_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

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
    public function getAll(TransactionRepository $repo,CompteRepository $rep,Request $req):Response
    {
        $client=$rep->find(1);

        $date1=$req->get('date1');
        $date2=$req->get('date2');
        $sum = $repo->sumTransaction($client, $date1, $date2);



        $res =$repo->findListByDate($client,$date1,$date2);
        if($req->get('ajax')){
            return new JsonResponse([
                'content'=>$this->renderView('clientdash/transaction/table.html.twig',[
                    'transac'=> $res,
                    'date1'=>$date1,
                    'date2'=>$date2,
                    "sum"=>$sum,
                    "compte"=>$client

                ]),
                'data'=>$this->renderView('clientdash/transaction/index.html.twig',[
                'transac'=> $res,
                'date1'=>$date1,
                'date2'=>$date2,
                "sum"=>$sum,
                "compte"=>$client])

            ]);
        }

        return $this->render('clientdash/transaction/TransactionsHistory.html.twig',[
            'transac'=> $res,
            "sum"=>$sum,
            "date1"=>$date1,
            "date2"=>$date2,
            "compte"=>$client

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
        $transaction->setCompteRecus($client->getId());
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
            if($client->getSolde()>$transaction->getMontant()){
                $client->setSolde($client->getSolde()-$transaction->getMontant());
                $em->persist($client);
                $em->persist($transaction);
                $em->flush();
                return $this->redirectToRoute('trans');
            }else{
                $this->addFlash('failed',"You don't have that much money to transfert");
                return $this->redirectToRoute('trans');
            }
        }
        return $this->render('clientdash/transaction/transfert.html.twig',['form'=>$form->createView(),'cl'=>$client]);
    }
    #[Route('/withdrawl' ,name:'retrait')]
    public function withdrawal(Request $req, ManagerRegistry $mg,CompteRepository $rep):Response
    {
        $em = $mg->getManager();
        $transaction=new Transaction();
        $client=$rep->find(1);
        $transaction->setIdCompte($client);
        $transaction->setTypeTransaction(TransactionType::Retrait);
        $transaction->setCompteRecus($client->getId());
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
                $this->addFlash('failed',"You don't have that much money to withrow");
                return $this->redirectToRoute('retrait');
            }

        }
        return $this->render('clientdash/transaction/withdrawl.html.twig',['form'=>$form->createView(),'cl'=>$client]);
    }


    #[Route('/getAllTransactionsForStaff',name:'getAll')]
    public function getAllTransactions(TransactionRepository $rep ,Request $req):Response
    {
        $id=$req->get('id');

        $res=$rep->findListById($id);

        if($req->get('ajax')){

            return new JsonResponse([
                'content'=>$this->renderView('staffdash/transactions/transactionTable.html.twig',[
                    'res'=> $res,])]);



    }
            return $this->render('staffdash/transactions/getAllTransactions.html.twig',[
                'res'=>$res
            ]);



    }
    #[Route('/showDetails/{id}',name:'detail')]
    public function details(TransactionRepository $rep,$id,CompteRepository $crep,FactureRepository $frep):Response
    {

        $res=$rep->findOneById($id);


        return $this->json(['code'=>200,'transaction'=>$res], 200, [], ['groups' => 'transaction']);

    }

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
        $facture=$rep->findByIDTransactionOrNot($id);
        $transaction=$repo->find($id);
        $em=$mg->getManager();
        if($facture!=null){
        $em->remove($facture);
        $em->flush();
        }
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

    #[Route(name:'factTotale')]
    public function factureTotale(Request $req,TransactionRepository $rep,CompteRepository $repo): Response
    {
        $date1=$req->get('date1');
        $date2=$req->get('date2');
        $compte=$repo->find(1);
            $res = $rep->findListByDate($compte, $date1, $date2);
            $sum = $rep->sumTransaction($compte, $date1, $date2);


        return $this->render('clientdash/transaction/index.html.twig',[
                'transac'=> $res,
                'date1'=>$date1,
                'date2'=>$date2,
                "sum"=>$sum,
                "compte"=>$compte]);


        }


    #[Route('/mail',name:'mail')]
    public function sendMail(Request $req){
        if($req->get('mail')){
            $email = (new Email())
                ->from('domamain01@gmail.com')
                ->to('mohamedomarfitouri@gmail.com')
                ->subject('Transaction Facture')
                ->html('<h1>Hello</h1>');

            $dsn= 'gmail+smtp://domamain01@gmail.com:aqgpyzrrwvjcrejf@default';
            $transport= Transport::fromDsn($dsn);
            $mailer=new Mailer($transport);
            $mailer->send($email);

        }
    }

    #[Route('/detail/{id}',name:'detailTransac')]
    public function detail($id,TransactionRepository $rep):Response
    {
        $res=$rep->find($id);
        return $this->render('staffdash/transactions/detail.html.twig',[
            'tran'=>$res
        ]);
    }

    #[Route('/pdf/{id}',name:'pdf')]
    public function pdfgenerate(Request $req,$id,FactureRepository $repo,TransactionRepository $rep,CompteRepository $re):Response
    {
      $pdfOption = new Options();
      $pdfOption->set('defaultFont','Arial');
      $pdfOption->setIsRemoteEnabled(true);

      $dompdf=new Dompdf($pdfOption);
      $context= stream_context_create([
          'ssl' => [
              'verify_peer'=>False,
              'verify_peer_name'=>False,
              'allow_self_signed'=>True
          ]
      ]);
        $fact=$repo->findByIDTransaction($id);
        $tranc=$rep->find($id);
        $compte=$re->find($tranc->getIdCompte());
      $dompdf->setHttpContext($context);
      $html=$this->renderView('clientdash/transaction/tableFact.html.twig',['tranc'=>$tranc,'fact'=>$fact,'compte'=>$compte]);
      $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();
        $fichier='factureCompte='.$compte->getId().'.pdf';

        $dompdf->stream($fichier,[
            'Attachement'=>true
        ]);
        return new Response();
    }

  //  #[Route('/excel/{id}',name:'ExportExcel')]
    //public function exportExcel(Request $req,$id,FactureRepository $repo):JsonResponse
    //{


    //}

}
