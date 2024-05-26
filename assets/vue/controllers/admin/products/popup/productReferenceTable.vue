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
                    <img v-if="productReference.imageUrl" :src="productReference.imageUrl" alt="Product Image"
                        class="rounded h-12 w-auto mx-auto">
                    <span v-else>Aucune image</span>
                </td>
                <td class="px-4 py-2">
                    <div class="flex">
                        <button @click="selected('edit', productReference)"
                            class="font-light text-yellow-600 hover:text-yellow-500 hover:underline">
                            <i class="fa-solid fa-edit"></i>
                            Modifier
                        </button>
                        <button @click="toggleSoftDelete(productReference)"
                            class="flex-1 text-white text-center rounded-md px-2 py-1 mx-5" :class="{
                                'bg-red-900 hover:bg-red-950': productReference.deletedAt === null,
                                'bg-lime-600 hover:bg-lime-700': productReference.deletedAt !== null
                            }">
                            <i class="fa-trash fa-solid" v-if="productReference.deletedAt === null">
                            </i>
                            <i class="fa-solid fa-trash-arrow-up" v-else></i>
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    <productRefForm v-if="selectedProductReference !== null && selectedActionType === 'edit'"
        :product-ref="selectedProductReference" :api-token="apiToken" @on-form-canceled="reset"
        @on-product-reference-saved="handleProductReferenceSave" @on-product-reference-edit-error="emitParentToast">
    </productRefForm>
</template>

<script>
import editProductReference from './editProductReference.vue';
import axios from 'axios';

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
    components: {
        'productRefForm': editProductReference,
    },
    emits: ['onProductReferenceDeleted', 'onToastEmited', 'onProductReferenceSaved'],
    data() {
        return {
            currentProductReferences: null,
            selectedProductReference: null,
            selectedActionType: null,
        }
    },
    methods: {
        selected(action, ref) {
            this.selectedProductReference = ref;
            this.selectedActionType = action;
        },
        reset() {
            this.selectedProductReference = null;
            this.selectedActionType = null;
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
        emitParentToast(payload) {
            console.log(payload);
            this.$emit('onToastEmited', payload);
        },
        handleProductReferenceSave(payload) {
            this.selectedProductReference = null;
            this.selectedActionType = null;
            let index = this.currentProductReferences.findIndex((ref) => ref.id === payload.data.id);
            if (index !== -1) {
                this.currentProductReferences[index] = payload.data;
            }
            this.$emit('onProductReferenceSaved', payload);
        },
        toggleSoftDelete(targetedReference) {
            let index = this.currentProductReferences.findIndex((ref) => ref.id === targetedReference.id);
            if (index === -1) {
                this.emitParentToast({
                    messages: `La référence ${targetedReference.slug} n'est pas trouvable veuiller réessayer`,
                    type: "danger"
                })
                return;
            }
            axios.delete(`/api/admin/references/${targetedReference.id}/suppression`, {
                headers: {
                    "Authorization": `Bearer ${this.apiToken}`
                }
            })
                .then((res) => {
                    this.currentProductReferences[index].deletedAt = res.data.deletedAt;
                    this.emitParentToast({
                        messages: `La référence ${targetedReference.slug} est désormais en ${this.currentProductReferences[index].deletedAt !== null ? 'supprimé' : 'active'}`,
                        type: this.currentProductReferences[index].deletedAt !== null ? 'warning' : 'success'
                    })
                })
                .catch((err) => this.emitParentToast({
                    messages: err.response.data.error.msg.map((err) => err.message),
                    type: "danger"
                }));
        },
    },
    beforeMount() {
        this.currentProductReferences = [...this.productReferences];
    }
}
</script>