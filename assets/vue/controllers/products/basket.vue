<template>
    <popup 
        v-if="popUpOpenState"
        :basket="cartItems"
        @close-popup="popUpOpenState = false"
    ></popup>
    <div>
      <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-md py-4 px-6 z-20 flex">
        <p @click="popUpOpenState = true" class="mr-10 hover:underline"><i class="fa-solid fa-shopping-basket px-10"></i>Votre panier : {{ totalQuantity }} Produit{{ totalQuantity > 1 ? 's' : '' }}</p>
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