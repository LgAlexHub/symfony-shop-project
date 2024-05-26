<template>
    <div>
        <h1 @click="isVisible = !isVisible" class="cursor-pointer text-center my-3 text-2xl text-gray-800 hover:text-blue-600">
            Todo List
            <i :class="{'rotate-180': isVisible}" class="ml-2 fas fa-chevron-down"></i>
        </h1>
        <div :class="{ 'hidden': !isVisible, 'bg-gray-100': isVisible }" class="px-4 py-2 mx-4 my-6 border border-gray-300 rounded-lg shadow-md space-y-8">
            <ul class="list-none border-b border-red-700">
                <span class="font-bold text-red-900">
                    [AMELIORATION|FEATURE|CORRECTION] à faire au plus vite car nécessaire au fonctionnement du site
                </span>
                <ul class="[&>*]:ml-10 list-disc text-red-600">
                    <li>Ajuster un composant vue dans la popup product admin pour créer un productReference à faire le [26/05/24]</li>
                    <li>Ajouter reset filtre bo qui reset la totalité des inputs à faire le [26/05/24]</li>
                    <li>Ajouter un init de mail admin pour initialiser un compte via un lien à faire le [26/05/24]</li>
                </ul>
            </ul>
            <ul class="list-none border-b border-yellow-700">
                <span class="font-semibold text-yellow-800">
                    [AMELIORATION|FEATURE|CORRECTION] pas nécessaire mais important à mettre en place
                </span>
                <ul class="[&>*]:ml-10 list-disc text-yellow-700">
                    <li>Ajuster le component toast pour qu'il colle toujours en bas d'écran</li>
                    <li>Normer tout mes fichiers vue et php (nommage, case, etc...)</li>
                    <li>Factoriser la pagination(admin) en la mettant dans un composant à part</li>
                    <li>Enlever les slugs au product reference</li>
                    <li>Trouver un moyen d'isoler les composant vue admin et client, pour que l'utilisateur ne charge pas des composants qu'il n'utilisera jamais !</li>
                </ul>
            </ul>
            <ul class="list-none border-b border-sky-700">
                <span class=" font-light text-sky-700">
                    [AMELIORATION] bonus
                </span>
                <ul class="[&>*]:ml-10 list-disc text-sky-500">
                    <li>Ajouter un event listenner qui ecoutera toutes les requetes sur les controller admin api et qui vérifira si l'utilisateur possède un bearer token</li>
                    <li>Ajouter une propriété couleur à ProductCategory.</li>
                    <li>Déplacer la constante alias dans order et product repository dans les entités</li>
                    <li>Rajouter un on:hover dans la page nos produits qui change l'image, pour donner une utilité à toutes les photos faites</li>
                    <li>Regarder l'optimisation des images, voir si on doit les compresser pour le rendu</li>
                    <li>Regarde les //TODO dans VSCODE</li>
                    <li>NOTE : Les descriptions des produits ne servent pour l'instant à rien</li>
                </ul>
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