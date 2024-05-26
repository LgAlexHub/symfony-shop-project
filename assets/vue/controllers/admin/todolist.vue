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
                        <li class="text-gray-500">
                            <ul class="list-decimal	[&>*]:ml-10">  
                                <li>Normer tout mes fichiers vue et php (nommage, case, etc...)</li>
                                <li>Factoriser la pagination(admin) en la mettant dans un composant à part</li>
                                <li>Ajouter un filtre bo pour les produits afin de trier par favoris </li>
                                <li>Ajouter reset filtre bo qui reset la totalité des inputs</li>
                                <li>Ajuster le component toast pour qu'il colle toujours en bas d'écran</li>
                                <li>Ajuster un composant vue dans la popup product admin pour créer un productReference</li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <div class="text-green-600">
                    <i class="fas fa-arrow-up mr-2 text-green-400"></i> À planifier :
                    <ul class="[&>*]:ml-10 list-disc">
                        <li class="text-green-500">Ajouter un init de mail admin pour initialiser un compte via un lien</li>
                        <li class="text-green-500">Ajouter un event listenner qui ecoutera toutes les requetes sur les controller admin api et qui vérifira si l'utilisateur possède un bearer token</li>
                        <li class="text-green-500">Enlever les slugs au product reference</li>
                        <li class="text-green-500">Ajouter une propriété couleur à ProductCategory.</li>
                        <li class="text-green-500">Trouver un moyen d'isoler les composant vue admin et client, pour que l'utilisateur ne charge pas des composants qu'il n'utilisera jamais !</li>
                        <li class="text-green-500">Déplacer la constante alias dans order et product repository dans les entités</li>
                    </ul>
                </div>
                <div class="text-sky-600">
                    <i class="fas fa-arrow-up mr-2"></i> Note :
                    <ul class="[&>*]:ml-10 list-disc">
                        <li>Lors d'un update product via la popup + api , il faut modifier le updatedAt pour qu'il soit à jour</li>
                        <li class="">Les descriptions des produits ne servent pour l'instant à rien</li>
                        <li>Rajouter un on:hover dans la page nos produits qui change l'image, pour donner une utilité à toutes les photos faites</li>
                        <li>Regarder l'optimisation des images, voir si on doit les compresser pour le rendu</li>
                        <li class="">Regarde les //TODO dans VSCODE</li>
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