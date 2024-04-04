<template>
    <div>
        <h1 @click="isVisible = !isVisible" class="text-center my-3 text-2xl font-san underline text-slate-800">Todo list<i class="ml-3 fa-solid fa-circle-down"></i></h1>
        <div :class="{ 'hidden': !isVisible, 'bg-gray-200': isVisible }" class="px-2 py-1 mx-2 my-4 border-2 border-red-700 rounded">
            
            <ul class="list-none">
                <li>
                    <i class="fa-solid fa-check"></i>
                    <strong class="line-through">- Finir la création de commande</strong>
                </li>
                <li class="line-through font-bold">
                    <i class="fa-solid fa-check"></i>
                    Gérer les commandes le back-office
                    <ul class="[&>li]:indent-12">
                        <li>* Afficher les produits de la commandes</li>
                        <li>* Afficher si la commandes est complète (possède des produits)</li>
                        <li>* Ajouter une addresse de livraison dans la commande</li>
                        <li>* Afficher les infos d'adresse dans le back-office</li>
                        <li>* Ajouter une variable pour dire si l'owner du site à déjà gérer la commande</li>
                        <li>* Ajouter dans orders.index le total du panier pour chaque commande</li>
                        <li>* Ajouter le prix du panier total dans orders.show</li>
                    </ul>
                </li>
                <li class="text-black font-bold line-through">
                    <i class="fa-solid fa-check"></i>
                    Affichage front 
                    <ul class="[&>*]:indent-12">
                        <li>* Paginer les résultats pour alléger les requêtes de la bdd</li>
                        <li>* Ajouter des filtres par catégories</li>
                    </ul>
                </li>
                <li class="text-slate-600">
                    <i class="fa-solid fa-hourglass-start"></i>
                    Améliorer l'ui/ux de la prise de commande commande
                    <ul class="[&>*]:indent-12">
                        <li>* Afficher une liste de produit au chargement de la page , le tout paginer</li>
                        <li>* Rendre la page responsive</li>
                        <li>* Améliorer le desgin</li>
                    </ul>
                </li>
                <li class="text-slate-600">
                    <i class="fa-solid fa-hourglass-start"></i>
                    Back-Office
                    <ul class="[&>*]:indent-12">
                        <li>* Ajouter un endroit dans le bo pour ajouter des produits phares pour l'instant limité à 3 :
                            <ul class="[&>*]:indent-24">
                                <li>-- Ajouter un bouton ajouter au produit phare</li>
                                <li>-- Amélioration : Ajouter un carrousel</li>
                            </ul>
                        </li>
                        <li>* Ajouter des filtres dans le BO pour produits, commandes </li>
                        <li>* Ajouter la fonctionnalité d'upload d'image pour les productReferences</li>
                    </ul>
                </li>
                <li class="text-sky-600">
                    <i class="fa-solid fa-arrow-up"></i> Amélioration à faire plus tard
                    <ul class="[&>*]:indent-12">
                        <li class="line-through">* Ajouter une fixture pour Order et OrderProductRef</li>
                        <li>* Supprimer order.show.twig</li>
                        <li>* Ne pu actualiser la bdd à chaque clique côté client lors de la sélection product</li>
                        <li>* Ajouter une page récap de commande , possibilité de générer un pdf</li>
                        <li>* Montrer les étapes pour passer une commande</li>
                        
                    </ul>
                </li>
                <div class="text-lime-600">
                    <i class="fa-solid fa-arrow-up"></i> Idée à planifier
                    <ul class="[&>*]:indent-12">
                        <li>Implémenter vue-store pour la prise de commande</li>
                        <li>Auto delete des commandes non valides (avec validation des produits)</li>
                    </ul>
                </div>
            </ul>
        </div>
    </div>
</template>

<script>


export default {
    name: "todolist",
  
    data() {
        return {
            isVisible: false,
        }
    },
}
</script>