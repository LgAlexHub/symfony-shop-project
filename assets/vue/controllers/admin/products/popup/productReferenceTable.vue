<template>
    <table v-if="selectedProductReference === null" class="table-fixed w-full text-center border border-gray-300">
        <thead class="bg-gray-800 text-white">
            <tr>
                <th class="px-4 py-2">Prix</th>
                <th class="px-4 py-2">Quantité</th>
                <th class="px-4 py-2">Unité</th>
                <th class="px-4 py-2">Image</th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(productReference, index) in currentProductReferences"
                :key="'product_ref_' + productReference.id"
                :class="{ 'bg-white': index % 2 === 0, 'bg-blue-50': index % 2 !== 0 }">
                <td class="px-4 py-2">{{ (productReference.formatedPrice).toFixed(2) }} €</td>
                <td class="px-4 py-2">{{ productReference.weight }}</td>
                <td class="px-4 py-2">{{ productReference.weightType }}</td>
                <td class="px-4 py-2">
                    <img v-if="productReference.imageUrl" :src="productReference.imageUrl"
                        alt="Product Image" class="rounded h-12 w-auto mx-auto">
                    <span v-else>Aucune image</span>
                </td>
                <td class="px-4 py-2">
                    <div class="flex">
                        <button @click="selected('edit', productReference)" class="font-light text-yellow-600 hover:text-yellow-500 hover:underline">
                            <i class="fa-solid fa-edit"></i>
                            Modifier
                        </button>
                        <button @click="selected('delete', productReference)" class="font-light text-white border-2 border-red-500 rounded bg-red-500 hover:bg-red-700">
                            <i class="fa-solid fa-trash"></i>
                            Supprimer
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    <productRefForm
        v-if="selectedProductReference !== null && selectedActionType === 'edit'"
        :product-ref="selectedProductReference"
        :api-token="apiToken"
        @on-form-canceled="selectedProductReference = null; selectedActionType = null"
        @on-product-reference-saved="handleProductReferenceSave"
    ></productRefForm>
</template>

<script>
import editProductReference from './editProductReference.vue';
export default {
    name: "popupProductRefTable",
    props: {
        productReferences: {
            required: true,
            type: Array
        },
        apiToken: {
            required: true,
            type: String
        }
    },
    components : {
        'productRefForm' : editProductReference,
    },
    data() {
        return {
            currentProductReferences: null,
            selectedProductReference : null,
            selectedActionType : null,
        }
    },
    methods: {
        selected(action , ref){
            this.selectedProductReference = ref;
            this.selectedActionType = action;
        },
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
        handleProductReferenceSave(payload) {
            this.selectedProductReference = null;
            this.selectedActionType = null;
            let index = this.currentProductReferences.findIndex((ref) => ref.id === payload.id);
            if (index !== -1) {
                this.currentProductReferences[index] = payload;
            }
        },
    },
    beforeMount() {
        this.currentProductReferences = [...this.productReferences];
    }
}
</script>