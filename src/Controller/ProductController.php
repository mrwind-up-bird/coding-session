<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class ProductController extends AbstractController
{
    private const CSV_FIELD_SEPERATOR = ';';
    private const CSV_FIELD_ENCLOSURE = '"';

    public function __construct(private ProductRepository $productRepository, private SerializerInterface $serializer, private EntityManagerInterface $entityManager) {

    }

    #[Route('/', name: 'app_root')]
    public function index(): Response
    {
        $products = $this->productRepository->findAll();

        return $this->render('products/products.html.twig', ['products' => $products]);
    }

    #[Route('/upload', name: 'app_product_upload')]
    public function upload(Request $request): Response
    {
        $form = $this->createForm(FileType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->getData();

            if ($uploadedFile && $this->isCsvFile($uploadedFile)) {
                $this->createOrUpdateProducts($uploadedFile);
                $this->addFlash('success', 'File successfully uploaded...');
            }
            else {
                $this->addFlash('error', 'Error uploading File');
            }
            $this->redirectToRoute('app_product_upload');
        }

        return $this->render('upload/upload_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function createOrUpdateProducts($file)
    {
        $context = [
            'csv_delimiter' => self::CSV_FIELD_SEPERATOR,
            'csv_enclosure' => self::CSV_FIELD_ENCLOSURE,
        ];

        $content = $this->removeBomFromUtf8String(file_get_contents($file));
        $data = $this->serializer->decode($content, 'csv', $context);

        foreach ($data AS $row) {
            $product = $this->productRepository->findOneByProductId($row['Produktnummer']);
            if (!$product) {
                $product = new Product();
            }
            $product->setProductId($row["Produktnummer"]);
            $product->setProductName($row["Produktname"]);
            $product->setProductPrice(floatval($row["Preis"]));
            $product->setProductTaxValue(intval($row["Mwst_Prozent"]));
            $product->setProductDescription($row["Beschreibung"]);

            $this->entityManager->persist($product);
        }

        $this->entityManager->flush();
    }

    private function isCsvFile($file) : bool
    {
        return $file->getClientOriginalExtension() === 'csv';
    }

    /**
     * removes BOM Chars from UTF8 String.
     */
    private function removeBomFromUtf8String(string $string): string
    {
        if (substr($string, 0, 3) == chr(hexdec('EF')).chr(hexdec('BB')).chr(hexdec('BF'))) {
            return substr($string, 3);
        } else {
            return $string;
        }
    }
}
