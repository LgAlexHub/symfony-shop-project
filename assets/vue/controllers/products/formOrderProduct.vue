<template>
    <div>
        
        <h1>Produits</h1>
        <table class="table-fixed w-full border-separate border-spacing-2" v-if="productsOrder.length > 0">
            <thead>
                <tr>
                    <td>Nom</td>
                    <td>Poids</td>
                    <td>Unité</td>
                    <td>Prix</td>
                </tr>
            </thead>
            <tbody>
                <template  v-for="item in productsOrder" :key="item.slug">
                    <tr>
                        <td class="bg-slate-600 border border-slate-300 text-white px-2 py-1">{{ item.product.name }}</td>
                        <td class="bg-slate-600 border border-slate-300 text-white px-2 py-1">{{ item.weight }}</td>
                        <td class="bg-slate-600 border border-slate-300 text-white px-2 py-1">{{ item.weightType }}</td>
                        <td class="bg-slate-600 border border-slate-300 text-white px-2 py-1">{{ (item.price/100).toFixed(2) }} €</td>
                    </tr>
                </template>
            </tbody>
        </table>
        <input class="bg-white-600 border border-slate-300 px-2 py-1" type="text" v-model="queryString" @input="handleQueryInput">
        <input type="hidden" id="order[items]" name="order[items]" :value="serializedItems">
        <div>
            <select v-if="fetchedProducts.length > 0" name="" id="">
                <template v-for="fetchedProduct in fetchedProducts" :key="fetchedProduct.slug">
                    <template v-if="fetchedProduct.productReferences.length > 0">
                        <option @change="addFetchProductToOrder(fetchedProduct, fetchedProductReference)" :value="fetchedProductReference.slug" v-for="fetchedProductReference in fetchedProduct.productReferences" :key="fetchedProductReference.slug">
                            {{ fetchedProduct.name }} {{ fetchedProductReference.weight }} {{ fetchedProductReference.weightType }} - {{ (fetchedProductReference.price / 100).toFixed(2) }} €
                        </option>
                    </template>
                </template>
            </select>
            <p v-else>
                Aucun produit à ajouter à la commande
            </p>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    export default {
        props : {
            linkItem : String
        },
        data(){
            return {
                productsOrder: [],
                fetchedProducts : [],
                queryString : "",
                debounceTimeout : null,
                selectedFetchProduct : null
            }
        },
        computed:{
            serializedItems(){
                return JSON.stringify(this.productsOrder);
            }
        },
        methods: {
            addFetchProductToOrder(fetchedProduct, fetchProductRef){
                concole.log(fetchProductRef, fetchedProduct)
            },
            handleQueryInput(){
                clearTimeout(this.debounceTimeout);
                this.debounceTimeout = setTimeout(() => {
                    this.fetchProductsByQuery()
                }, 0.5 * 1000)
            },
            fetchProductsByQuery(){
                axios.get(`/produits/recherche?nom=${this.queryString}`)
                    .then((res) => {
                        this.fetchedProducts = res.data
                    })
                    .catch((err) => {
                        console.error(err)
                        // throw client feedback
                    })
            }
        },
        // {price: 320, weight: 120, weightType: 'g', product: {…}, slug: 'confiture-d-abricots-120g'}
        mounted(){
            this.productsOrder = [JSON.parse(this.linkItem)];
        }
    }
</script>