<?php
// src/Controller/BirthdayController.php

namespace App\Controller;

use App\Entity\Birthday;
use App\Repository\BirthdayRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api")
 */

class BirthdayController extends AbstractController
{
    private $entityManager;
    private $birthdayRepository;
    private $validator;

    public function __construct(EntityManagerInterface $entityManager, BirthdayRepository $birthdayRepository, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->birthdayRepository = $birthdayRepository;
        $this->validator = $validator;
    }

/**
 * @Route("/birthday", name="create_birthday", methods={"POST"})
 */
public function create(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager): Response
{
    $data = json_decode($request->getContent(), true);

    $birthday = new Birthday();
    $birthday->setName($data['name'] ?? null);

    try {
        if (isset($data['birthday'])) {
            $birthday->setBirthday(new \DateTime($data['birthday']));
        }
    } catch (\Exception $e) {
        // Gérer l'erreur de création de DateTime
        return $this->json(['error' => 'DATE INVALIDE.'], Response::HTTP_BAD_REQUEST);
    }

    // Valider l'entité Birthday
    $errors = $validator->validate($birthday);

    if (count($errors) > 0) {
        // S'il y a des erreurs de validation, renvoyer les détails des erreurs
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }
        return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
    }

    $entityManager->persist($birthday);
    $entityManager->flush();

    return $this->json(['id' => $birthday->getId()], Response::HTTP_CREATED);
}
    /**
     * @Route("/birthday", name="get_birthdays", methods={"GET"})
     */
    public function getAllBirthdays(): JsonResponse
    {
        $birthdays = $this->birthdayRepository->findAll();

        // Créez un tableau pour stocker les données à renvoyer en JSON
        $responseData = [];

        // Boucle à travers chaque objet Birthday pour extraire les données nécessaires
        foreach ($birthdays as $birthday) {
            $responseData[] = [
                'id' => $birthday->getId(),
                'name' => $birthday->getName(),
                'birthday' => $birthday->getBirthday()->format('Y-m-d') // Formatage de la date au format ISO 8601
            ];
        }

        // Retourne la réponse JSON avec les données formatées
        return $this->json($responseData);
    }

    /**
     * @Route("/birthday/{id}", name="delete_birthday", methods={"DELETE"})
     */
    public function delete(Birthday $birthday): Response
    {
        $this->entityManager->remove($birthday);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/birthday/{id}/update", name="update_birthday", methods={"PATCH"})
     */
    public function update(Request $request, Birthday $birthday, ValidatorInterface $validator, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $birthday->setName($data['name']);
        }

        if (isset($data['birthday'])) {
            try {
                $birthday->setBirthday(new \DateTime($data['birthday']));
            } catch (\Exception $e) {
                // Gérer l'erreur de création de DateTime
                return $this->json(['error' => 'Date Invalide.'], Response::HTTP_BAD_REQUEST);
            }
        }

        // Valider l'entité Birthday
        $errors = $validator->validate($birthday);

        if (count($errors) > 0) {
            // S'il y a des erreurs de validation, renvoyer les détails des erreurs
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return $this->json($birthday);
    }
}
