<?php

namespace App\Controller\Trait;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity as Entity;

trait ControllerToolsTrait
{

    /**
     * Undocumented function
     *
     * @param mixed $var
     * @param integer $id
     * @return void
     */
    protected function checkEntityExistence(mixed $var, string $slug)
    {
        if (!$var) {
            throw $this->createNotFoundException("No entity found for slug : $slug");
        }
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @param FormInterface $form
     * @return void
     */
    protected function handleAndCheckForm(Request $request, FormInterface $form)
    {
        $form->handleRequest($request);
        return $form->isSubmitted() && $form->isValid();
    }

    /**
     * Return referer attribute from request's headers 
     *
     * @param Request $request
     * @return string
     */
    protected function getRefererUrl(Request $request): string
    {
        return $request->headers->get("referer");
    }


    protected function renderWithNavigation(mixed $currentEntity, string $templatePath, array $clientReturnedData): Response
    {
        return  $this->render($templatePath, [
            "urls" => $this->generateUrlTree($currentEntity),
            "lastLabel" => $currentEntity->getValueToSlugify(),
            // "back" => $this->buildUrlTreeFromEntity($currentEntity, $labelEntity),
            ...$clientReturnedData
        ]);
    }

    protected function renderWithRefererUrl(Request $request, string $templatePath, array $clientReturnedData) : Response
    {
        return $this->render($templatePath, [
            "url" => ["label" => "Retour", "route" => $request->headers->get('referer')],
            "lastLabel" => null,
            ...$clientReturnedData
        ]);
    }

    /**
     * Undocumented function
     *
     * @param mixed $entity
     * @return void
     */
    private function getParentEntity(mixed $entity){
        return match(true){
            is_a($entity, Entity\Product::class)          => null,
            is_a($entity, Entity\ProductCategory::class)  => null,
            is_a($entity, Entity\ProductReference::class) => $entity->getProduct(),
            default                                       => null,
        };
    }

    /**
     * Undocumented function
     *
     * @param mixed $entity
     * @return void
     */
    private function getParentRoute(mixed $entity){
        return match(true){
            is_a($entity, Entity\Product::class)          => ["label" => "Produits", "route" => "admin.products.index"],
            is_a($entity, Entity\ProductCategory::class)  => ["label" => "Catégories", "route" => "admin.categories.index"],
            is_a($entity, Entity\ProductReference::class) => ["label" => $entity->getProduct()->getName() , "route" => "admin.products.references.index", "slug" => $entity->getProduct()->getSlug()],
            default                                       => ["label" => "Produits", "route" => "admin.products.index"],
        };
    }

    /**
     * Undocumented function
     *
     * @param mixed $entity
     * @return void
     */
    private function generateUrlTree(mixed $entity){
        $currentNode = $entity;
        $urlTree = [];
        while ($currentNode !== null){
            $urlTree[] = $this->getParentRoute($currentNode);
            $currentNode = $this->getParentEntity($currentNode);
        }
        return array_reverse($urlTree);
    }





}
