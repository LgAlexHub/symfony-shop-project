<template>
    <popup 
        v-if="popUpOpenState"
        :basket="cartItems"
        @close-popup="popUpOpenState = false"
    ></popup>
    <div class="fixed bottom-0 left-0 right-0 bg-brownmecha shadow-md h-16 px-6 z-20 flex justify-arround items-center">
        <div>
            <p @click="popUpOpenState = true" class="mr-10 hover:underline text-white"><i class="fa-solid fa-shopping-basket px-2"></i>Votre panier : {{ totalQuantity }} Produit{{ totalQuantity > 1 ? 's' : '' }}</p>
        </div>
        <div>
            <form action="/commandes/commander" method="POST">
                <input type="hidden" name="basket_items" :value="JSON.stringify(cartItems)">
                <button v-if="cartItems.length > 0" class="bg-[#4C7C80] hover:bg-green-600 text-white font-bold py-1 px-4 rounded focus:outline-none focus:ring-2 focus:ring-green-400" type="submit">Commander</button>
            </form>
        </div>
    </div>
  </template>
<script>
import popup from "./popup.vue";
export default {
    name : "basket",
    props: {
        cartItems: {
            type: Array,
            required: true
        }
    },
    components : {
        'popup' : popup
    },
    emits: ['alterItemQuantity', 'removeItem'],
    data() {
        return {
            popUpOpenState : false,
        }
    },
    computed : {
        totalQuantity(){
            return this.cartItems.reduce((acc, currentCartItem) => acc += currentCartItem.quantity, 0);
        }
    }
}
</script>