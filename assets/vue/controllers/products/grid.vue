<template>
    <div class="h-30 flex justify-center my-8">
        <nav aria-label="Page navigation example">
            <ul class="list-style-none flex">
                <li v-if="paginator.page > 1">
                    <a class="relative hover:bg-black hover:text-white block border-rounded border-2 border-black rounded px-3 py-1.5 text-sm text-black" @click="goToPage(paginator.page - 1)"><i class="fa-solid fa-arrow-left-long"></i></a>
                </li>
                <li aria-current="page">
                    <p class="relative border-rounded border-2 border-black mr-3 ml-3 block rounded px-3 py-1.5 text-sm text-black">{{ paginator.page }}</p>
                </li>
                <li v-if="totalPage > paginator.page" aria-current="page">
                    <a class="relative hover:bg-black hover:text-white block border-rounded border-2 border-black rounded px-3 py-1.5 text-sm text-black" @click="goToPage(paginator.page + 1)">
                        <i class="fa-solid fa-arrow-right-long"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <div class="mb-40">
        <div class="grid grid-cols-12">
            <div class="col-span-2 px-5">
                <h4 class="text-2xl my-4">Catégories</h4>
				<ul class="text-md space-y-3">
                    <li v-for="category in categories" :key="'category_'+category.label">
                        <a class="hover:underline" :class="{ 'font-bold' : selectedCategory == category.id}" @click="selectCategory(category.id)">
                            {{category.label}}
                        </a>
                    </li>                        
                    <li>
                        <a class="hover:underline" :class="{ 'font-bold' : selectedCategory == null}" @click="selectCategory(null)">
                            Tous
                        </a>
                    </li>   
				</ul>
			</div>
            <div class="col-span-9">
                <div class="grid grid-cols-4 gap-10">
                    <div v-for="product in paginator.products" :key="'product_'+product.slug" class="max-w-sm rounded hover:border-4 hover:border-red-300 overflow-hidden shadow-lg transition ease-in-out delay-150 hover:-translate-y-1 hover:scale-105 duration-300">
                            <img v-if="product.productReferences.length > 0" :src="'/products/'+product.productReferences[0].slug+'.jpg'" class="w-full" alt="">
                            <img v-else src="/products/default_product.jpg" class="w-full" alt="">
                        <div class="px-6 py-4">
                            <div class="font-bold text-md mb-2">{{ product.name }}</div>
                        </div>
                        <div class="px-6 pt-4 pb-2 sm:block sm:justify-center">
                            <span @click="addBasket(ref, product.name)" v-for="(ref, index) in product.productReferences" :class="'block text-center rounded-full hover:bg-fuchsia-950 hover:text-white px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2 bg-purple-'+(index + 1)*100">
                                {{ref.weight}} {{ref.weightType}} - {{ (ref.formatedPrice).toFixed(2) }} €
                            </span>
                        </div>
                    </div>
				</div>
            </div>
        </div>
        <bar @alter-item-quantity="alterQuantity" @remove-item="removeItemFromBasket" :cart-items="cart"></bar>
    </div>
</template>

<script>
import axios from 'axios';
import basket from "./basket.vue";

export default {
    components : {
        'bar' : basket
    },
    data(){
        return {
            selectedCategory : null,
            categories : [],
            selectedProduct : null,
            paginator : {
                page : 1,
                totalPage : null,
                products : [],
                resultCount : null,
            },
            queryInput: "",
            queryDebounceTimeout : null,
            orderByDebounceTimeout : null,
            cart : [],
        }
    }, 
    methods : {
        fetchCartItemIndexBySlug(slug){
            return this.cart.findIndex((cartItem) => cartItem.reference.slug === slug);
        },
        debouncedProductInputHandler() {
        // to bind on input text
            clearTimeout(this.queryDebounceTimeout);
            this.queryDebounceTimeout = setTimeout(() => {
                this.page = 1;
                this.fetchPaginatedProducts();
            }, 0.8 * 1000);
        },
        selectCategory(id){
            this.selectedCategory = id;
            this.page = 1;
            this.fetchPaginatedProducts();
        },
        addBasket(ref, productName){
            let refIndex = this.fetchCartItemIndexBySlug(ref.slug);
            if (refIndex === -1){
                this.cart.push({
                    quantity : 1,
                    productName : productName,
                    reference : ref
                });
            }else {
                this.cart[refIndex].quantity+= 1;
            }

        },
        alterQuantity(eventPayload){
            let refIndex = this.fetchCartItemIndexBySlug(eventPayload.slug);
            if (refIndex === -1){
                //TODO : Ajouter feetabck
                return ;
            }

            if(eventPayload.isAddition){
                this.cart[refIndex].quantity +=1;
                return;
            }

            if(this.cart[refIndex].quantity === 1){
                this.cart.splice(refIndex, 1);
                return;
            }
            this.cart[refIndex].quantity -= 1;
        },
        removeItemFromBasket(eventPayload){
            let refIndex = this.fetchCartItemIndexBySlug(eventPayload.slug);
            if (refIndex !== -1){
                this.cart.splice(refIndex, 1);
            }
        },
        debouncedOrderByInputHandler() {
        // to bind on input text
            clearTimeout(this.orderByDebounceTimeout);
            this.orderByDebounceTimeout = setTimeout(() => {
                this.page = 1;
                this.fetchPaginatedProducts();
            }, 0.5 * 1000);
        },
        goToPage(nextPage = 1){
            if(nextPage < 1 || nextPage > this.totalPage)
                //TODO : Ajouter un toast de feedback
                return;
            this.page = nextPage;
            this.fetchPaginatedProducts()

        },
        fetchPaginatedProducts(){
            let paramString = "?page="+this.paginator.page;

            if(this.selectedCategory !== null){
                paramString+="&cat="+this.selectedCategory;
            }

            if (this.queryInput !== ""){
                paramString+="&query="+this.queryInput;
            }
            
            // if(this.columnsToUrl.length !== 0){
            //     paramString+=this.columnsToUrl;
            // }

            axios.get(`/api/produits${paramString}`, {})
                .then((res) => {
                    this.paginator.page = res.data.page;
                    this.paginator.totalPage = res.data.totalPage;
                    this.paginator.resultCount = res.data.nbResult;
                    this.paginator.products = res.data.products;
                   
                });
        },
        fetchProductCategories(){
            axios.get(`/api/produits/categories`, {})
                .then((res) => {
                    this.categories = res.data;
                });
        },
    },
    created(){
        this.fetchProductCategories();
        this.fetchPaginatedProducts();
    }
}
</script>