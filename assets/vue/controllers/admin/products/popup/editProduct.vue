<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <div class="mb-4">
                <label for="privateId" class="block text-sm font-semibold mb-1">Identifiant privé :</label>
                <p id="privateId" class="w-full px-4 py-2 rounded-md border border-gray-300 bg-gray-100">{{
                    currentProduct.id }}</p>
            </div>
            <div class="mb-4">
                <label for="registrationDate" class="block text-sm font-semibold mb-1">Produit enregistré le :</label>
                <p id="registrationDate" class="w-full px-4 py-2 rounded-md border border-gray-300 bg-gray-100">{{
                    timesptampToEuTimzezoneString(currentProduct.createdAt.timestamp) }}</p>
            </div>
            <div class="mb-4">
                <label for="lastModifiedDate" class="block text-sm font-semibold mb-1">Dernière modification :</label>
                <p id="lastModifiedDate" class="w-full px-4 py-2 rounded-md border border-gray-300 bg-gray-100">{{
                    timesptampToEuTimzezoneString(currentProduct.updatedAt.timestamp) }}</p>
            </div>
        </div>
        <div>
            <div class="mb-4">
                <label for="productName" class="block text-sm font-semibold mb-1">Nom du produit :</label>
                <input type="text" id="productName" v-model="currentProduct.name"
                    class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label for="productDescription" class="block text-sm font-semibold mb-1">Description :</label>
                <textarea id="productDescription" v-model="currentProduct.description"
                    class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:border-blue-500 focus:ring-blue-500 resize-none"></textarea>
            </div>
            <div v-if="isProductDifferent" class="mb-4">
                <button @click="handleEditProduct" class="bg-slate-500 w-full hover:bg-slate-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Modifier
                </button>
            </div>
        </div>
    </div>
</template>


<script>
import axios from 'axios';
export default {
    name: "popupProductEdit",
    props: {
        product: {
            required: true,
            type: Object
        },
        apiToken: {
            required: true,
            type: String
        }
    },
    emits : ['onProductSaved'],
    data() {
        return {
            currentProduct: null,
        }
    },
    computed : {
        isProductDifferent(){
            return this.currentProduct.description !== this.product.description || this.currentProduct.name !== this.product.name && this.currentProduct.name !== "";
        }
    },
    methods: {
        timesptampToEuTimzezoneString(timestamp) {
            return new Date(timestamp * 1000)
                .toLocaleString('fr-FR', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
        },
        handleEditProduct() {
            axios.patch(`/api/admin/produits/${this.currentProduct.id}`, {
                name : this.currentProduct.name,
                description : this.currentProduct.description
            }, {
                headers: {
                    "Authorization": `Bearer ${this.apiToken}`
                }
            })
                .then((resolve) => {
                    this.$emit('onProductSaved', resolve.data)
                    //TODO : Ajouter un toast feedback
                })
                .catch((error) => console.error(error));
        }
        
    },
    beforeMount() {
        this.currentProduct = Object.assign({}, this.product);
    }
}
</script>