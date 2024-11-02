<?php

// src/Controller/CompanyController.php

namespace App\Controller;

use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Name;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CompanyController extends AbstractController
{
    #[Route('/companies', name: 'company_create', methods: ['GET', 'POST'])]
    public function handle(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        if ($request->isMethod('GET')) {
            $companies = $entityManager->getRepository(Company::class)->findBy([], ['id' => 'DESC']);

            return $this->render('company/index.html.twig', [
                'companies' => $companies,
            ]);
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            if (!isset($data['name'], $data['vatNumber'], $data['street'], $data['houseNo'], $data['busNo'], $data['postcode'], $data['city'])) {
                return $this->json(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
            }

            $company = new Company();
            $company->setName($data['name']); 
            $company->setVatNumber($data['vatNumber']); 
            $company->setStreet($data['street']); 
            $company->setHouseNo((int)$data['houseNo']); 
            $company->setBusNo($data['busNo']); 
            $company->setPostcode($data['postcode']); 
            $company->setCity($data['city']); 

            $errors = $validator->validate($company);
            if (count($errors) > 0) {
                return $this->json(['error' => (string) $errors], Response::HTTP_BAD_REQUEST);
            }

            $entityManager->persist($company);
            $entityManager->flush();

            // Add flash message for success
            $this->addFlash('success', 'Company "' . $company->getName() . '" with VAT number ' . $company->getVatNumber() . ' was created successfully!');


            // Redirect to the company listing page
            return $this->redirectToRoute('company_create');
        }

        return $this->json(['error' => 'Company data not available'], Response::HTTP_METHOD_NOT_ALLOWED);
    }
    #[Route('/companies/delete/{id}', name: 'company_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
        $company = $entityManager->getRepository(Company::class)->find($id);

        if (!$company) {
            $this->addFlash('error', 'Company not found.');
            return $this->redirectToRoute('company_create');
        }

        $entityManager->remove($company);
        $entityManager->flush();

        $this->addFlash('success', 'Company "' . $company->getName() . '" was deleted successfully.');

        return $this->redirectToRoute('company_create');
    }
}