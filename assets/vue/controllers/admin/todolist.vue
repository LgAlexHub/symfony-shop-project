<template>
    <div>
        <h1 @click="isVisible = !isVisible" class="cursor-pointer text-center my-3 text-2xl text-gray-800 hover:text-blue-600">
            Todo List
            <i :class="{'rotate-180': isVisible}" class="ml-2 fas fa-chevron-down"></i>
        </h1>
        <div :class="{ 'hidden': !isVisible, 'bg-gray-100': isVisible }" class="px-4 py-2 mx-4 my-6 border border-gray-300 rounded-lg shadow-md">
            <ul class="list-none">
                <li class="text-gray-600">
                    <i class="fas fa-hourglass-start mr-2 text-gray-400"></i>
                    Back-Office :
                    <ul class="[&>*]:ml-10 list-disc">
                        <li class="text-gray-500">Ajouter un endroit dans le BO où on peut ajouter des produits phares.</li>
                        <li class="text-gray-500">Ajouter la fonctionnalité d'upload d'image pour les productReferences.</li>
                        <li class="text-gray-500">Ajouter un menu déroulant pour choisir parmis toutes les catégories de produit en bdd, puis afficher uniquement les produits appartenant à cette catégorie /admin/produits</li>
                    </ul>
                </li>
                <li class="text-gray-600">
                    <i class="fas fa-hourglass-start mr-2 text-gray-400"></i>
                    Faire une refonte complète du système de prise de commande [côté client] :
                    <ul class="[&>*]:ml-10 list-disc">
                        <li class="text-gray-500">Composant Vue dynamique.</li>
                        <li class="text-gray-500">Page responsive.</li>
                        <li class="text-gray-500">Beau design.</li>
                        <li class="text-gray-500">Montrer les étapes pour passer une commande.</li>
                        <li class="text-gray-500">Implémenter vue-store pour la prise de commande (à voir).</li>
                    </ul>
                </li>
                <li class="text-blue-600">
                    <i class="fas fa-arrow-up mr-2 text-blue-400"></i> Amélioration à faire plus tard :
                    <ul class="[&>*]:ml-10 list-disc">
                        <li class="text-blue-500">Configurer symfony sur le bon fuseau horaire</li>
                        <li class="text-blue-500">Ajouter une page récap de commande , possibilité de générer un pdf.</li>
                        <li class="text-blue-500">Ajouter une propriété couleur à ProductCategory.</li>
                    </ul>
                </li>
                <div class="text-green-600">
                    <i class="fas fa-arrow-up mr-2 text-green-400"></i> Idée à planifier :
                    <ul class="[&>*]:ml-10 list-disc">
                        <li class="text-green-500">Trouver un moyen d'isoler les composant vue admin et client, pour que l'utilisateur ne charge pas des composants qu'il n'utilisera jamais !</li>
                        <li class="text-green-500">Ajouter une propriété pour savoir si le client a bien payé le site owner.</li>
                        <li class="text-green-500">Déplacer la constante alias dans order et product repository dans les entités</li>
                        <li class="text-green-500">Gérer les temps d'attentes après une requête axios dans les composant vue.js (ajouter une anim de chargement + desac boutton)</li>
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