<template>
    <div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 z-50">
        <div class="bg-white rounded-lg shadow-lg w-2/4">
            <div class="bg-[#804C62] border-[#804C62] flex justify-between px-4 py-2 rounded-t-lg">
                <h2 class="text-lg text-white font-bold">
                    Votre panier
                </h2>
                <button @click="$emit('closePopup')" class="text-white hover:text-gray-800 focus:outline-none">
                    <i class="fa-solid fa-x"></i>
                </button>
            </div>
            <div class="h-80 overflow-y-scroll">
                <div>
                    <!-- Your basket items loop -->
                    <div v-for="item in basket" :key="'product_' + item.reference.slug" class="flex items-center border-b border-gray-200 mx-2 px-10 py-4">
                        <!-- Product Image -->
                        <div class="flex-shrink-0 mr-4">
                            <img :src="item.reference.imageUrl" class="w-16 h-16 rounded-md object-cover" alt="Product Image">
                        </div>
                        <!-- Product Details -->
                        <div class="flex-grow mr-4">
                            <p class="font-bold text-md mb-1">{{ item.productName }}</p>
                            <p class="text-gray-700"><span class="font-bold">Poids:</span> {{ item.reference.weight }} {{ item.reference.weightType }}</p>
                        </div>
                        <!-- Unit Price -->
                        <div class="flex-shrink-0 mr-4">
                            <p class="text-gray-700">Prix à l'unité: <strong>{{ item.reference.formatedPrice.toFixed(2) }} €</strong></p>
                            <p class="text-gray-700">Quantité: <strong>{{ item.quantity }}</strong></p>
                            <p class="text-gray-700">Prix du lot: <strong>{{ (item.quantity * item.reference.formatedPrice).toFixed(2) }} €</strong></p>
                        </div>
                        <!-- Buttons -->
                        <div class="flex-shrink-0">
                            <button @click="$parent.$emit('alterItemQuantity', {slug : item.reference.slug, isAddition : false})" class="mr-2 px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 focus:outline-none">-</button>
                            <button @click="$parent.$emit('alterItemQuantity', {slug : item.reference.slug, isAddition : true})" class="mr-2 px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 focus:outline-none">+</button>
                            <button @click="$parent.$emit('removeItem', {slug : item.reference.slug})" class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600 focus:outline-none">Supprimer</button>
                        </div>
                    </div>
                    <!-- End of basket items loop -->
                </div>
            </div>
            <div class="h-30 flex flex-col justify-center items-center">
                <p class="text-lg font-bold mb-2">Total du panier: {{ totalCartPrice.toFixed(2) }} €</p>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        basket: {
            type: Array,
            required: true,
        }
    },
    name: "popup",
    emits: ['closePopup', 'alterItemQuantity', 'removeItem'],
    data() {
        return {

        }
    },
    computed : {
        totalCartPrice(){
            return this.basket.reduce((acc, currentCartItem) => acc+= (currentCartItem.reference.formatedPrice * currentCartItem.quantity), 0.0)
        }
    }

}
</script>