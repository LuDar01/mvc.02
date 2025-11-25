<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use App\Entity\Book;
use App\Repository\BookRepository;

final class LibraryController extends AbstractController
{
    #[Route('/library', name: 'library_home')]
    public function index(): Response
    {
        // Omdirigera till listan över alla böcker för att undvika en tom landningssida.
        // Eller rendera en välkomstsida med länkar till CRUD.
        return $this->redirectToRoute('library_show_all'); 
    }

#[Route('/library/create', name: 'library_create', methods: ['GET', 'POST'])]
    public function createBook(
        ManagerRegistry $doctrine,
        Request $request
    ): Response {
        if ($request->isMethod('POST')) {
            $entityManager = $doctrine->getManager();
            $book = new Book();

            // 1. Hantera bilduppladdning
            /** @var UploadedFile|null $imageFile */
            $imageFile = $request->files->get('image_file');
            
            $imagePath = 'public/img/logotyp.png'; // Standardvärde
            
            if ($imageFile) {
                // Skapa ett unikt filnamn
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                
                // Flytta filen till din 'public/uploads/images' katalog
                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/images',
                        $newFilename
                    );
                    $imagePath = '/uploads/images/' . $newFilename;

                } catch (\FileException $e) {
                    // Hantera eventuella filuppladdningsfel här
                    $this->addFlash('error', 'Kunde inte ladda upp filen: ' . $e->getMessage());
                    return $this->redirectToRoute('library_create');
                }
            }
            
            // 2. Sätt övriga fält och den nya bildsökvägen
            $book->setTitle($request->request->get('title'));
            $book->setIsbn($request->request->get('isbn'));
            $book->setAuthor($request->request->get('author'));
            $book->setImage($imagePath); // Spara den genererade sökvägen
            
            // 3. Spara till databas
            $entityManager->persist($book);
            $entityManager->flush();

            $this->addFlash('success', 'Boken "' . $book->getTitle() . '" lades till i biblioteket!');

            return $this->redirectToRoute('library_show_one', ['id' => $book->getId()]);
        }

        return $this->render('library/create.html.twig');
    }

    #[Route('/library/show', name: 'library_show_all')]
    public function showAllBooks(
        BookRepository $bookRepository // Doctrine injicerar automatiskt
    ): Response {
        $books = $bookRepository->findAll();

        return $this->render('library/show.html.twig', [
            'books' => $books,
        ]);
    }

    #[Route('/library/show/{id}', name: 'library_show_one', methods: ['GET'])]
    public function showOneBook(
        BookRepository $bookRepository,
        int $id
    ): Response {
        $book = $bookRepository->find($id);

        if (!$book) {
            // Skapar ett 404-fel om boken inte hittas
            throw $this->createNotFoundException('Boken hittades inte.');
        }

        return $this->render('library/show_one.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/library/update/{id}', name: 'library_update', methods: ['GET', 'POST'])]
    public function updateBook(
        ManagerRegistry $doctrine,
        Request $request,
        int $id
    ): Response {
        $entityManager = $doctrine->getManager();
        $book = $entityManager->getRepository(Book::class)->find($id);
        
        // ... (check if book exists) ...

        if ($request->isMethod('POST')) {
            // ... (uppdatera titel, isbn, author) ...
            $book->setTitle($request->request->get('title'));
            $book->setIsbn($request->request->get('isbn'));
            $book->setAuthor($request->request->get('author'));
            
            // Hantera bilduppladdning även vid uppdatering
            /** @var UploadedFile|null $imageFile */
            $imageFile = $request->files->get('image_file');

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                
                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/images',
                        $newFilename
                    );
                    
                    // OM det finns en gammal fil som inte är default.jpg, RADEra den.
                    if ($book->getImage() && $book->getImage() !== '/img/default.jpg' && file_exists($this->getParameter('kernel.project_dir') . '/public' . $book->getImage())) {
                        unlink($this->getParameter('kernel.project_dir') . '/public' . $book->getImage());
                    }
                    
                    $book->setImage('/uploads/images/' . $newFilename);

                } catch (\FileException $e) {
                    $this->addFlash('error', 'Kunde inte ladda upp ny fil: ' . $e->getMessage());
                }
            }
            
            // ... (Spara ändringarna till databasen och omdirigera) ...
            $entityManager->flush();

            $this->addFlash('success', 'Boken uppdaterades!');
            return $this->redirectToRoute('library_show_one', ['id' => $book->getId()]);
        }

        return $this->render('library/update.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/library/delete/{id}', name: 'library_delete', methods: ['POST'])]
    public function deleteBook(
        ManagerRegistry $doctrine,
        int $id
    ): Response {
        $entityManager = $doctrine->getManager();
        $book = $entityManager->getRepository(Book::class)->find($id);

        if (!$book) {
            $this->addFlash('warning', 'Boken fanns redan inte, radering avbruten.');
            return $this->redirectToRoute('library_show_all');
        }

        // Steg 1: Tala om för Doctrine att denna entitet ska tas bort
        $entityManager->remove($book);
        
        // Steg 2: Utför borttagningen (DELETE-frågan)
        $entityManager->flush();

        $this->addFlash('success', 'Boken "' . $book->getTitle() . '" raderades framgångsrikt.');
        
        // Omdirigera till listan över alla böcker efter radering
        return $this->redirectToRoute('library_show_all');
    }
}
