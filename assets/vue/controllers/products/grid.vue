<template>
    <div class="h-30 flex justify-center my-8">
        <nav aria-label="Page navigation example">
            <ul class="list-style-none flex">
                <li v-if="paginator.page > 1">
                    <button
                        class="relative block border-2 bg-[#80504C] hover:bg-amber-950 rounded-xl px-3 py-1.5 lg:text-sm text-2xl text-white"
                        @click="goToPage(paginator.page - 1)"><i class="fa-solid fa-arrow-left-long"></i></button>
                </li>
                <li aria-current="page">
                    <p
                        class="relative border-rounded border-2 mr-3 ml-3 block rounded-xl bg-[#804C62] text-white px-3 py-1.5 lg:text-sm text-2xl">
                        {{ paginator.page }}</p>
                </li>
                <li v-if="paginator.totalPage > paginator.page" aria-current="page">
                    <button
                        class="relative block border-2 bg-[#80504C] hover:bg-amber-950 rounded-xl px-3 py-1.5 lg:text-sm text-2xl text-white"
                        @click="goToPage(paginator.page + 1)">
                        <i class="fa-solid fa-arrow-right-long"></i>
                    </button>
                </li>
            </ul>
        </nav>
    </div>
    <div class="mb-40">
        <div class="grid grid-cols-12">
            <div class="lg:col-span-2 col-span-3 px-5">
                <h4 class="text-2xl my-4 rounded bg-[#804C62] text-center text-white py-2">Catégories</h4>
                <ul class="text-md space-y-3 text-center space-y-5">
                    <li>
                        <a @click="selectCategory('favorite')" :class="{
                            'font-bold text-white rounded-2xl bg-[#804C62]/70 mx-2': selectedCategory === 'favorite',
                            'hover:underline': selectedCategory !== 'favorite'
                        }" class="block transition text-[#80504C] duration-300 ease-in-out lg:text-md text-xl">
                            Nos recommandations
                        </a>
                    </li>
                    <li v-for="category in categories" :key="'category_' + category.label">
                        <a @click="selectCategory(category.id)" :class="{
                            'font-bold text-white rounded-2xl bg-[#804C62]/70 mx-2': selectedCategory === category.id,
                            'hover:underline': selectedCategory !== category.id,
                        }" class="block transition duration-300 ease-in-out lg:text-md text-xl">
                            {{ category.label }}
                        </a>
                    </li>
                    <li>
                        <a @click="selectCategory(null)" :class="{
                            'font-bold text-white rounded-2xl bg-[#804C62]/70 mx-2': selectedCategory === null,
                            'hover:underline': selectedCategory !== null
                        }" class="block transition duration-300 ease-in-out lg:text-md text-xl">
                            Tous
                        </a>
                    </li>
                </ul>

            </div>
            <div class="lg:col-span-9 col-span-8">
                <div v-if="!isLoading" class="grid grid-cols-2 lg:grid-cols-4 gap-10">
                    <div v-for="(product, index) in paginator.products" :key="'product_' + index"
                        class="max-w-sm rounded hover:border-4 hover:border-[#804C7C] overflow-hidden shadow-lg transition ease-in-out delay-150 hover:-translate-y-1 hover:scale-105 duration-300">
                        <img v-if="product.productReferences.length > 0"
                            :src="'/products/' + product.productReferences[0].slug + '.jpg'" class="w-full" alt="">
                        <img v-else src="/products/default_product.jpg" class="w-full" alt="">
                        <div class="px-6 py-4">
                            <div class="font-bold text-2xl lg:text-md mb-2">{{ product.name }}</div>
                        </div>
                        <div class="px-6 pt-4 pb-2 sm:block sm:justify-center">
                            <div @click="addBasket(ref, product.name)"
                                v-for="(ref, index) in product.productReferences"
                                :class="'block text-center rounded-full flex hover:bg-fuchsia-950 lg:text-sm hover:font-bold text-2xl text-gray-700 mr-2 mb-2 bg-gradient-to-r from-purple-'+ (300 + (index + 1)* 100)+' to-white'">
                                <span class="flex-1 border-r-4 border-r-white hover:text-white font-semibold">{{ (ref.formatedPrice).toFixed(2) }} €</span><span class="flex-1"> {{ ref.weight }} {{ ref.weightType }}</span> 
                            </div>
                        </div>
                    </div>
                </div>
                <loadingSpinner v-else></loadingSpinner>
            </div>
        </div>
        <bar @alter-item-quantity="alterQuantity" @remove-item="removeItemFromBasket" :cart-items="cart"></bar>
    </div>
</template>


<script>
import axios from 'axios';
import basket from "./basket.vue";
import Spinner from "../components/Spinner.vue";

export default {
    components: {
        'bar': basket,
        'loadingSpinner' : Spinner
    },
    data() {
        return {
            isLoading: false,
            selectedCategory: null,
            categories: [],
            selectedProduct: null,
            paginator: {
                page: 1,
                totalPage: 0,
                products: [],
                resultCount: null,
            },
            queryInput: "",
            queryDebounceTimeout: null,
            orderByDebounceTimeout: null,
            cart: [],
        }
    },
    methods: {
        fetchCartItemIndexBySlug(slug) {
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
        selectCategory(id) {
            this.selectedCategory = id;
            this.page = 1;
            this.fetchPaginatedProducts();
        },
        addBasket(ref, productName) {
            let refIndex = this.fetchCartItemIndexBySlug(ref.slug);
            if (refIndex === -1) {
                this.cart.push({
                    quantity: 1,
                    productName: productName,
                    reference: ref
                });
            } else {
                this.cart[refIndex].quantity += 1;
            }

        },
        alterQuantity(eventPayload) {
            let refIndex = this.fetchCartItemIndexBySlug(eventPayload.slug);
            if (refIndex === -1) {
                //TODO : Ajouter feetabck
                return;
            }

            if (eventPayload.isAddition) {
                this.cart[refIndex].quantity += 1;
                return;
            }

            if (this.cart[refIndex].quantity === 1) {
                this.cart.splice(refIndex, 1);
                return;
            }
            this.cart[refIndex].quantity -= 1;
        },
        removeItemFromBasket(eventPayload) {
            let refIndex = this.fetchCartItemIndexBySlug(eventPayload.slug);
            if (refIndex !== -1) {
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
        goToPage(nextPage = 1) {
            if (nextPage < 1 || nextPage > this.totalPage) {
                //TODO : Ajouter un toast de feedback
                return;
            }
            this.paginator.page = nextPage;
            this.fetchPaginatedProducts();

        },
        fetchPaginatedProducts() {
            this.isLoading = true;
            let paramString = "?page=" + this.paginator.page;

            if (this.selectedCategory !== null) {
                paramString += "&cat=" + this.selectedCategory;
            }

            if (this.queryInput !== "") {
                paramString += "&query=" + this.queryInput;
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
                    this.isLoading = false;
                });
        },
        fetchProductCategories() {
            axios.get(`/api/produits/categories`, {})
                .then((res) => {
                    this.categories.push(...res.data);
                });
        },
    },
    mounted() {
        this.fetchProductCategories();
        this.fetchPaginatedProducts();
    }
}
</script>