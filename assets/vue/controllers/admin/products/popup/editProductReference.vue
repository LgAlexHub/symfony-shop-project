<template v-if="currentProductRef !== null">
    <p class="text-2xl font-semibold mb-4">
        Modification de la référence : {{ currentProductRef.slug }}
    </p>
    <form v-if="!isLoading" class="space-y-6" @submit.prevent="submitForm">
        <div>
            <label for="weight" class="block text-sm font-medium text-gray-700">Quantité</label>
            <div class="mt-1">
                <input type="number" id="weight" v-model="currentProductRef.weight"
                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
            </div>
        </div>
        <div>
            <label for="weightType" class="block text-sm font-medium text-gray-700">Unité</label>
            <div class="mt-1">
                <input type="text" id="weightType" v-model="currentProductRef.weightType"
                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
            </div>
        </div>
        <div>
            <label for="price" class="block text-sm font-medium text-gray-700">Prix</label>
            <div class="mt-1">
                <input type="number" step="0.01" id="price" v-model="currentProductRef.formatedPrice"
                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
            </div>
        </div>
        <div class="flex justify-end space-x-4">
            <button @click.prevent="$emit('onFormCanceled')"
                class="px-4 py-2 text-sm font-medium text-red-500 rounded focus:outline-none hover:underline focus:ring-2 focus:ring-offset-2 focus:ring-red-400">
                <i class="fa-solid fa-arrow-left"></i>
                Retour à la liste
            </button>
            <button @click="onProductReferenceEditForm" type="submit"
                class="px-4 py-2 text-sm rounded font-medium text-lime-600 focus:outline-none hover:bg-lime-800 hover:text-white">
                Valider
            </button>
        </div>
    </form>
    <spinner v-if="isLoading"></spinner>
</template>

<script>
    import axios from 'axios';
import Spinner from '../../../components/Spinner.vue';
    export default {
        name: "popupEditProductRef",
        components : {
          'spinner' : Spinner
        },
        props: {
            productRef: {
                required: true,
                type: Object
            },
            apiToken: {
                required: true,
                type: String
            }
        },
        emits : ["onFormCanceled", "onProductReferenceSaved", "onProductReferenceEditError"],
        data() {
            return {
                currentProductRef: null,
                isLoading : false,
            }
        },
        methods: {
            onProductReferenceEditForm() {
                this.isLoading = true;
                axios.patch(`/api/admin/references/${this.currentProductRef.id}`, {
                    price: this.currentProductRef.formatedPrice * 100,
                    weight: this.currentProductRef.weight,
                    weightType: this.currentProductRef.weightType,
                }, {
                    headers: {
                        "Authorization": `Bearer ${this.apiToken}`
                    }
                })
                    .then((resolve) => {
                        this.$emit("onProductReferenceSaved", {
                            data :  resolve.data,
                            messages : "La référence produit a été modifié avec succès",
                            type : "success"
                        });
                    }) 
                    .catch((error) => this.$emit('onProductReferenceEditError', {
                        messages : error.response.data.error.msg.map((err) => err.message),
                        type : "danger"
                    }))
                    .then((_) => this.isLoading = false);
            }
        },
        beforeMount() {
            this.currentProductRef = Object.assign({}, this.productRef);
        }
    }
</script>