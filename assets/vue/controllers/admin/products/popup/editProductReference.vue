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
        <div class="py-2">
            <label for="fileUpload" class="block text-sm font-medium text-gray-700">Téléversement d'image</label>
            <input id="fileUpload" type="file" name="imageFile" @change="onFileChanged" class="hidden">
            <button @click="triggerFileForm" class="bg-teal-700 hover:bg-teal-800 text-gray-100 rounded py-2 px-4">
                Upload Image
            </button>
            <div class="flex flex-row gap-4 divide-x">
                <div class="flex-1 flex flex-col items-center justify-center">
                    <h4>Avant Enregistrement</h4>
                    <img :src="currentProductRef.imageUrl" alt="Preview" class="h-20 rounded" />
                </div>
                <div class="flex-1 flex flex-col items-center justify-center">
                    <h4>Après Enregistrement</h4>
                    <img :src="imageUrl" alt="Preview" class="h-20 rounded" v-if="imageUrl" />
                    <p v-else
                        class="h-20 bg-gray-100 hover:bg-gray-200 rounded border-black border text-center px-2 flex items-center justify-center">
                        Aucune image en cours d'upload
                    </p>
                </div>
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
    components: {
        'spinner': Spinner
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
    emits: ["onFormCanceled", "onProductReferenceSaved", "onProductReferenceEditError"],
    data() {
        return {
            currentProductRef: null,
            isLoading: false,
            file: null,
            imageUrl: null,
            allowedImageTypes: [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/bmp',
                'image/webp',
                'image/tiff',
                'image/svg+xml',
                'image/x-icon',
                'image/heif',
                'image/heic'
            ]
        }
    },
    methods: {
        onProductReferenceEditForm() {
            let formData = new FormData();
            formData.append('price', this.currentProductRef.formatedPrice * 100);
            formData.append('weight', this.currentProductRef.weight);
            formData.append('weightType', this.currentProductRef.weightType);
            if (this.file !== null) {
                formData.append('imageFile', this.file);
            }

            this.isLoading = true;
            axios.post(`/api/admin/references/${this.currentProductRef.id}`, formData, {
                headers: {
                    "Authorization": `Bearer ${this.apiToken}`,
                }
            })
                .then((resolve) => {
                    this.$emit("onProductReferenceSaved", {
                        data: resolve.data,
                        messages: "La référence produit a été modifié avec succès",
                        type: "success"
                    });
                })
                .catch((error) => this.$emit('onProductReferenceEditError', {
                    messages: error.response.data.error.msg.map((err) => err.message),
                    type: "danger"
                }))
                .then((_) => this.isLoading = false);
        },
        triggerFileForm() {
            document.getElementById('fileUpload').click();
        },
        onFileChanged(event) {
            if (event.target && event.target.files && this.allowedImageTypes.includes(event.target.files[0].type)) {
                this.file = event.target.files[0];
                const reader = new FileReader();
                reader.onload = (e) => this.imageUrl = e.target.result;
                reader.readAsDataURL(this.file);
            }else{
                this.$emit('onProductReferenceEditError', {
                    messages: ['Le fichier que vous tentez d\'uploader n\'est pas valide'],
                    type: "warning"
                })
            }
        }
    },
    beforeMount() {
        this.currentProductRef = Object.assign({}, this.productRef);
    }
}
</script>