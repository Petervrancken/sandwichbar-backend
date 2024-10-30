<?php

// src/Controller/CompanyController.php

namespace App\Controller;

use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class CompanyController extends AbstractController
{
    #[Route('/companies', name: 'company_create', methods: ['GET', 'POST'])]
    public function handle(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        
        if ($request->isMethod('GET')) {
            // Handle GET request: fetch all companies
            $companies = $entityManager->getRepository(Company::class)->findAll();

            // Check if the request expects JSON (like for an API call)
            if ($request->headers->get('Accept') === 'application/json') {
                return new JsonResponse($companies);
            }

            return $this->render('company/index.html.twig', [
                'companies' => $companies,
            ]);
        }

        if ($request->isMethod('POST')) {
             // Handle POST request: create a new company

            // Use $request->request to get form data
            $data = $request->request->all();

            // Log the submitted data for debugging
            dump($data); // This will show the submitted form data
            
            // Ensure all required fields are present
            if (!isset($data['name'], $data['vatNumber'], $data['street'], $data['houseNo'], $data['busNo'], $data['postcode'], $data['city'])) {
                return $this->json(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
            }
            

            // Create a new Company entity and set its properties
            $company = new Company();
            $company->setName($data['name']); 
            $company->setVatNumber($data['vatNumber']); 
            $company->setStreet($data['street']); 
            $company->setHouseNo((int)$data['houseNo']); 
            $company->setBusNo($data['busNo']); 
            $company->setPostcode($data['postcode']); 
            $company->setCity($data['city']); 

            // Validate the entity (optional, but recommended)
            $errors = $validator->validate($company);
            if (count($errors) > 0) {
                return $this->json(['error' => (string) $errors], Response::HTTP_BAD_REQUEST);
            }

            // Persist and save the entity
            $entityManager->persist($company);
            $entityManager->flush();

            return $this->json(['message' => 'Company created successfully'], Response::HTTP_CREATED);
        }

        // If method is not GET or POST (though it should be caught by the route config)
        return $this->json(['error' => 'Method not allowed'], Response::HTTP_METHOD_NOT_ALLOWED);
    }
}
