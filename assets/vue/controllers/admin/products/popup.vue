<template>
    <toast :data-messages="feedbackMsg" :data-type="feedbackType"></toast>
    <div v-if="currentProduct !== null"
        class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 z-40">
        <div class="bg-white rounded-lg shadow-lg w-2/4">
            <div class="bg-gray-200 flex justify-between px-4 py-2 rounded-t-lg">
                <h2 class="text-lg font-bold">
                    {{ currentProduct.name }}
                </h2>
                <button @click="$emit('closePopup')" class="text-gray-600 hover:text-gray-800 focus:outline-none">
                    <i class="fa-solid fa-x"></i>
                </button>
            </div>
            <div class="tabs border-b border-gray-200 flex">
                <button v-for="(tab, index) in tabs"
                    :key="index"
                    @click="activeTab = tab"
                    class="tablink px-4 py-2 focus:outline-none hover:bg-slate-600 hover:text-white flex-1"
                    :class="{'bg-slate-800 text-white' : activeTab === tab}"
                >
                    {{ tab }}
                </button>
            </div>
            <div class="p-8">
                <editProductTab
                    v-if="activeTab === 'Produit'" 
                    :product="currentProduct" 
                    :categories="categories"
                    :api-token="apiToken"
                    @on-product-saved="handleProductSave"
                    @on-product-edit-error="handleFeedback"
                ></editProductTab>
                <prodRefTable
                    v-else
                    :product-references="product.productReferences"
                    :api-token="apiToken"
                    @on-product-reference-saved="handleFeedback"
                    @on-product-reference-edit-error="handleFeedback"
                    ></prodRefTable>
            </div>
        </div>
    </div>
</template>

<script>
import editProduct from './popup/editProduct.vue';
import productReferenceTable from './popup/productReferenceTable.vue';
import Toast from "../../components/Toast.vue";
export default {
    name: "popup",
    props: {
        product: {
            required: true,
            type: Object
        },
        categories : {
            required: true,
            type: Array
        },
        apiToken: {
            required: true,
            type: String
        }
    },
    emits: ['closePopup', 'onProductReferenceDelete', 'onProductSave'],
    components: {
        'editProductTab' : editProduct,
        'prodRefTable'   : productReferenceTable,
        'toast'          : Toast
    },
    data() {
        return {
            currentProduct: null,
            currentProductReference: null,
            tabs : ['Produit', 'Prix & Tarifs'],
            activeTab : 'Produit',
            feedbackMsg : null,
            feedbackType : null,
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
        handleProductReferenceSave(payload) {
            this.currentProductReference = null;
            let index = this.currentProduct.productReferences.findIndex((ref) => ref.id === payload.data.id);
            if (index !== -1) {
                this.currentProduct.productReferences[index] = payload.data;
            }
        },
        handleProductSave(payload){
            this.currentProduct = payload.data;
            this.handleFeedback(payload);
            this.$emit('onProductSave', payload.data);
        },
        handleFeedback(payload){
            this.feedbackMsg = payload.messages;
            this.feedbackType = payload.type;
        }
    },
    beforeMount() {
        this.currentProduct = Object.assign({}, this.product);
    }
}
</script>