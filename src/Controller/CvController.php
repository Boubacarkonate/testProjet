<?php

namespace App\Controller;

use App\Entity\Cv;
use App\Form\CvType;
use App\Service\FileUploader;
use App\Repository\CvRepository;
use App\Service\PdfService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/cv')]
class CvController extends AbstractController
{
    #[Route('/', name: 'app_cv_index', methods: ['GET'])]
    public function index(CvRepository $cvRepository): Response
    {
        return $this->render('cv/index.html.twig', [
            'cvs' => $cvRepository->findAll(),
        ]);
    }
/************************************************************************************* */
    #[Route('/pdf/{id}', name: 'app_cv_pdf', methods: ['GET'])]
    public function generatePdf(Cv $cv, PdfService $pdfService) 
    {
                                                                                    //generer pdf avec le service

       $html = $this->render('default/mypdf.html.twig', [
        'cv' => $cv,
       ]);
       $pdfService->showPdfFile($html);
    }



/************************************************************************************************ */
    #[Route('/new', name: 'app_cv_new', methods: ['GET', 'POST'])]
    public function new(FileUploader $fileUploader ,Request $request, CvRepository $cvRepository): Response
    {
        $cv = new Cv();
        $form = $this->createForm(CvType::class, $cv);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $cv_annonce = $form->get('first_name')->getData();
            
            if ($cv_annonce) {
                
                $cv_annonceNom = $fileUploader->upload($cv_annonce);

                $cv->setFirst_name($cv_annonceNom);
            }

            $cvRepository->save($cv, true);

            return $this->redirectToRoute('app_cv_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cv/new.html.twig', [
            'cv' => $cv,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cv_show', methods: ['GET'])]
    public function show(Cv $cv): Response
    {
        return $this->render('cv/show.html.twig', [
            'cv' => $cv,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_cv_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cv $cv, CvRepository $cvRepository): Response
    {
        $form = $this->createForm(CvType::class, $cv);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cvRepository->save($cv, true);

            return $this->redirectToRoute('app_cv_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cv/edit.html.twig', [
            'cv' => $cv,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cv_delete', methods: ['POST'])]
    public function delete(Request $request, Cv $cv, CvRepository $cvRepository): Response
    {


        $fichierPdf = $cv->getFirst_name();
        dd($fichierPdf);

        if ($this->isCsrfTokenValid('delete'.$cv->getId(), $request->request->get('_token'))) {
            $cvRepository->remove($cv, true);
        }

        return $this->redirectToRoute('app_cv_index', [], Response::HTTP_SEE_OTHER);
    }
}
