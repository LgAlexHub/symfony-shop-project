<template>
    <div class="grid grid-cols-5 gap-5 my-2">
        <div class="px-5 col-span-2 h-100 overflow-scroll">
            <p class="text-xl">Votre panier</p>
            <template v-for="orderItem in orderItems" :key="orderItem.item.slug">
                <div class="border-2 my-1 border-solid border-lime-950 rounded px-2 py-1 grid grid-cols-3">
                    <div class="grid grid-rows-2 col-span-2">
                        <p class="text-md">
                            {{ orderItem.item.product.name }} - {{ orderItem.item.weight }} {{ orderItem.item.weightType}}
                        </p>
                        <div class="grid grid-cols-3">
                            <p class="">{{ (orderItem.item.price/100).toFixed(2) }} €</p>
                            <div class="inline col-span-2 place-self-center py-1">
                                <button class="px-2 border-2 border-solid border-lime-950 rounded" @click="editQuantity(orderItem.item.slug)">
                                    <span class="">-</span>
                                </button>
                                <span class="px-2 mx-1 border-2 border-solid border-lime-950 rounded">{{ orderItem.quantity }}</span>
                                <button class="px-2 border-2 border-solid border-lime-950 rounded" @click="editQuantity(orderItem.item.slug, true)">
                                    +
                                </button>
                            </div>
                        </div>
                    </div>
                    <div>
                        <button class="border-2 bg-red-600 text-white hover:bg-red-900 text-xs border-solid border-lime-950 px-2 py-1 w-full h-1/2 my-5" @click="removeItem(orderItem.item.slug)">
                            Suppr.
                        </button>
                    </div>
                </div>
            </template>
        </div>
        <div class="col-span-3 px-2">
            <div class="border-2 border-solid border-lime-950 px-2 rounded-sm py-1 w-full">
                <ProductsFetcher @product-added="onProductAdded"></ProductsFetcher>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from "axios";
    import ProductsFetcher from './ProductsFetcher.vue';
    export default {
        components : {
            ProductsFetcher
        },
        props:{
            orderUuid : {
                type : String,
                required : true
            },
            prefetchOrderItem : {
                type : String,
                required : true
            }
        },
        data(){
            return {
                orderItems : [],
                productQueryString : '',
                debounceTimeout : null,
            }
        },
        // computed:{},
        methods:{
            editQuantity(slug, isAddition){
                let itemIndex = this.orderItemBySlug(slug);
                if (itemIndex === -1){
                    // throw error, pop up , feedback client
                }

                if (!isAddition){
                    this.orderItems[itemIndex].quantity = this.orderItems[itemIndex].quantity > 0 ? (this.orderItems[itemIndex].quantity - 1) : 0 ;
                }else{
                    this.orderItems[itemIndex].quantity += 1;
                }
            },
            removeItem(slug){
                let itemIndex = this.orderItemBySlug(slug);
                if (itemIndex !== -1){
                    this.orderItems.splice(itemIndex, 1);
                }
            },
            orderItemBySlug(slug){
                return this.orderItems.findIndex((element) => element.item.slug === slug);
            },
            onProductAdded(payloadItem){
                let itemIndex = this.orderItemBySlug(payloadItem.slug);
                if (itemIndex === -1){
                    itemIndex = this.orderItems.length;
                    this.orderItems[itemIndex] = {
                        quantity : 0,
                        item : {
                            price : payloadItem.price,
                            product : {
                                name : payloadItem.product.name,
                                slug : payloadItem.product.slug
                            },
                            slug : payloadItem.slug,
                            weight : payloadItem.weight,
                            weightType : payloadItem.weightType,
                        }
                    }
                }
                this.orderItems[itemIndex].quantity += 1;
                var bodyFormData = new FormData();
                bodyFormData.append('form[productReferenceSlug]', this.orderItems[itemIndex].item.slug);
                axios.post(`/api/commandes/${this.orderUuid}/ajouter-produit`, bodyFormData)
                .then((res) => console.log(res))
                .catch((err) => console.error(err))
            }
        },
        mounted(){
            this.orderItems = JSON.parse(this.prefetchOrderItem)
            // vm.$forceUpdate();
        }
    }
</script>