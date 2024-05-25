<template>
    <div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 z-50">
        <div class="bg-white rounded-lg shadow-lg w-10/12 sm:h-1/2 lg:w-2/4">
            <div class="bg-[#804C62] border-[#804C62] flex justify-between px-4 py-2 rounded-t-lg">
                <h2 class="text-lg text-white font-bold">
                    Votre panier
                </h2>
                <button @click="$emit('closePopup')" class="text-white hover:text-gray-800 focus:outline-none">
                    <i class="fa-solid fa-x"></i>
                </button>
            </div>
            <div class="h-3/4 overflow-y-scroll">
                <div>
                    <div v-for="item in basket" :key="'product_' + item.reference.slug" class="flex items-center border-b border-gray-200 mx-2 px-10 py-4 space-x-4">
                        <div class="flex-shrink-0 flex flex-col items-center justify-center text-center">
                            <p class="font-semibold text-brownmecha text-md mb-1">{{ item.productName }}</p>
                            <img :src="item.reference.imageUrl" class="w-32 h-32 rounded-md object-cover" alt="Product Image">
                        </div>
                        <div class="flex-grow space-y-1">
                            <p class="text-gray-600 text-extralight"><span class="font-semibold text-purplemecha-600">Poids:</span> {{ item.reference.weight }} {{ item.reference.weightType }}</p>
                            <p class="text-gray-600 text-extralight"><span class="font-semibold text-purplemecha-600">Prix à l'unité:</span> {{ item.reference.formatedPrice.toFixed(2) }} €</p>
                            <p class="text-gray-600 text-extralight"><span class="font-semibold text-purplemecha-600">Prix du lot: </span>{{ (item.quantity * item.reference.formatedPrice).toFixed(2) }} €</p>
                            <div class="flex flex-row space-x-4 items-center">
                                <p class="text-gray-600 text-extralight"><span class="font-semibold text-purplemecha-600">Quantité: </span>{{ item.quantity }}</p>
                                <button @click="$parent.$emit('alterItemQuantity', {slug : item.reference.slug, isAddition : true})" class="px-2 w-1/4 font-bold py-1 bg-teal-700 text-gray-200 rounded hover:bg-teal-800 focus:outline-none">+</button>
                                <button @click="$parent.$emit('alterItemQuantity', {slug : item.reference.slug, isAddition : false})" class="px-2 w-1/4 font-bold py-1 bg-gray-200 bg-rose-900 text-gray-200 rounded hover:bg-rose-950 focus:outline-none">-</button>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <button @click="$parent.$emit('removeItem', {slug : item.reference.slug})" class="px-2 py-1 sm:h-1/2 bg-red-800 text-white rounded hover:bg-red-900 focus:outline-none">Supprimer</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="h-30 flex flex-col justify-center items-center">
                <p class="text-lg font-semibold text-bluemecha mb-2">Total du panier: {{ totalCartPrice.toFixed(2) }} €</p>
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