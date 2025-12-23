<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException; // <- FileException fix
use App\Entity\Book;
use App\Repository\BookRepository;

final class LibraryController extends AbstractController
{
    #[Route('/library', name: 'library_home')]
    public function index(): Response
    {
        // Omdirigera till listan över alla böcker.
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

            // Hantera bilduppladdning
            /** @var UploadedFile|null $imageFile */
            $imageFile = $request->files->get('image_file');

            $imagePath = 'public/img/logotyp.png'; // Standardvärde

            if ($imageFile) {
                // Skapa ett unikt filnamn
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                // Flytta filen till 'public/uploads/images'
                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/images',
                        $newFilename
                    );
                    $imagePath = '/uploads/images/' . $newFilename;

                } catch (FileException $e) { // <- Correct class usage
                    // Hantera filuppladdningsfel
                    $this->addFlash('error', 'Kunde inte ladda upp filen: ' . $e->getMessage());
                    return $this->redirectToRoute('library_create');
                }
            }

            // Sätt övriga fält och bildsökvägen
            $book->setTitle($request->request->get('title'));
            $book->setIsbn($request->request->get('isbn'));
            $book->setAuthor($request->request->get('author'));
            $book->setImage($imagePath);

            // Spara till databas
            $entityManager->persist($book);
            $entityManager->flush();

            $this->addFlash('success', 'Boken "' . $book->getTitle() . '" lades till i biblioteket!');

            return $this->redirectToRoute('library_show_one', ['bookId' => $book->getId()]); // FIX: Changed 'id' to 'bookId'
        }

        return $this->render('library/create.html.twig');
    }

    #[Route('/library/show', name: 'library_show_all')]
    public function showAllBooks(
        BookRepository $bookRepository
    ): Response {
        $books = $bookRepository->findAll();

        return $this->render('library/show.html.twig', [
            'books' => $books,
        ]);
    }

    #[Route('/library/show/{bookId}', name: 'library_show_one', methods: ['GET'])] // <- Route path updated
    public function showOneBook(
        BookRepository $bookRepository,
        int $bookId // <- Variable renamed
    ): Response {
        $book = $bookRepository->find($bookId); // <- Usage updated

        if (!$book) {
            // Skapar ett 404-fel om boken inte hittas
            throw $this->createNotFoundException('Boken hittades inte.');
        }

        return $this->render('library/show_one.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/library/update/{bookId}', name: 'library_update', methods: ['GET', 'POST'])] // <- Route path updated
    public function updateBook(
        ManagerRegistry $doctrine,
        Request $request,
        int $bookId // <- Variable renamed
    ): Response {
        $entityManager = $doctrine->getManager();
        $book = $entityManager->getRepository(Book::class)->find($bookId); // <- Usage updated

        if ($request->isMethod('POST')) {
            // Uppdatera titel, isbn, author
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

                    // Radera den gamla filen om den finns och inte är standardbilden
                    if ($book->getImage() && $book->getImage() !== '/img/default.jpg' && file_exists($this->getParameter('kernel.project_dir') . '/public' . $book->getImage())) {
                        unlink($this->getParameter('kernel.project_dir') . '/public' . $book->getImage());
                    }

                    $imagePath = '/uploads/images/' . $newFilename;
                    $book->setImage($imagePath);

                } catch (FileException $e) { // <- Correct class usage
                    $this->addFlash('error', 'Kunde inte ladda upp ny fil: ' . $e->getMessage());
                }
            }

            // Spara ändringarna till databasen
            $entityManager->flush();

            $this->addFlash('success', 'Boken uppdaterades!');
            return $this->redirectToRoute('library_show_one', ['bookId' => $book->getId()]); // FIX: Changed 'id' to 'bookId'
        }

        return $this->render('library/update.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/library/delete/{bookId}', name: 'library_delete', methods: ['POST'])] // <- Route path updated
    public function deleteBook(
        ManagerRegistry $doctrine,
        int $bookId // <- Variable renamed
    ): Response {
        $entityManager = $doctrine->getManager();
        $book = $entityManager->getRepository(Book::class)->find($bookId); // <- Usage updated

        if (!$book) {
            $this->addFlash('warning', 'Boken fanns redan inte, radering avbruten.');
            return $this->redirectToRoute('library_show_all');
        }

        // Steg 1: Tala om för Doctrine att denna entitet ska tas bort
        $entityManager->remove($book);

        // Steg 2: Utför borttagningen
        $entityManager->flush();

        $this->addFlash('success', 'Boken "' . $book->getTitle() . '" raderades framgångsrikt.');

        // Omdirigera till listan över alla böcker efter radering
        return $this->redirectToRoute('library_show_all');
    }

    #[Route('/api/library/books', name: 'api_library_show_all', methods: ['GET'])]
    public function apiShowAllBooks(
        BookRepository $bookRepository
    ): JsonResponse {
        $books = $bookRepository->findAll();

        // Använder array_map för att konvertera varje Book-objekt till en array
        $data = array_map(function (Book $book) {
            return [
                'id' => $book->getId(),
                'title' => $book->getTitle(),
                'isbn' => $book->getIsbn(),
                'author' => $book->getAuthor(),
                'image' => $book->getImage(),
            ];
        }, $books);

        return new JsonResponse($data);
    }

    #[Route('/api/library/book/{isbn}', name: 'api_library_show_one_isbn_post_url', methods: ['POST'])]
    public function apiShowBookByIsbnPostUrl(
        BookRepository $bookRepository,
        string $isbn
    ): JsonResponse {
        // Använder findOneBy(['isbn' => $isbn]) för att hitta boken
        $book = $bookRepository->findOneBy(['isbn' => $isbn]);

        if (!$book) {
            return new JsonResponse([
                'error' => 'Book not found',
                'isbn' => $isbn
            ], Response::HTTP_NOT_FOUND); // Returnerar 404-status
        }

        $data = [
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'isbn' => $book->getIsbn(),
            'author' => $book->getAuthor(),
            'image' => $book->getImage(),
        ];

        return new JsonResponse($data);
    }
}
