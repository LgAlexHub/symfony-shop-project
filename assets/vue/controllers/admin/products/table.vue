<template>
    <popup v-if="selectedProduct !== null" :product="selectedProduct" :api-token="apiToken"
        :categories="productCategories" @close-popup="selectedProduct = null"
        @on-product-reference-delete="handleProductReferenceDelete" @on-product-save="handleProductSave"
        @on-feedback="toastFeedback"></popup>
    <toast :data-messages="feedback.msg" :data-type="feedback.type"></toast>
    <div class="pt-2 relative mx-auto text-gray-600">
        <input v-model="queryInput" @input="debouncedProductInputHandler"
            class="border-2 border-gray-300 bg-white w-full h-10 px-5 pr-16 rounded-lg text-sm focus:border-black"
            type="search" name="search" placeholder="Recherche par nom">
        <button type="submit" class="absolute right-0 top-0 mt-4 mr-4">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
    </div>
    <select @input="debouncedProductInputHandler" v-model="selectedCategory"
        class="block w-full px-4 py-2 my-5 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500">
        <option :value="null">Aucune catégorie</option>
        <option value="favorite">Nos recommandations</option>
        <option v-for="cat in productCategories" :key="cat.id" :value="cat.id">{{ cat.label }}</option>
    </select>
    <div>
        <h3 class="block">Filtrer par état de suppression</h3>
        <div class="flex ml-5 space-x-2">
            <template v-for="(state, index) in stateOptions" :key="'stateInput-'+index">
                <input type="radio" :value="state.value" :id="'radioState-' + index" v-model="selectedState"
                    @change="fetchPaginatedProduct">
                <label :for="'radioState-' + index">{{ state.label }}</label>
            </template>
        </div>
    </div>
    <nav class="my-12">
        <ul class="flex justify-center">
            <li v-if="page > 1">
                <button @click="goToPage(page - 1)"
                    class="mx-1 flex h-9 w-9 items-center justify-center rounded-full border border-blue-gray-100 bg-transparent p-0 text-sm text-blue-gray-500 transition duration-150 ease-in-out hover:bg-gray-300"
                    href="#" aria-label="Previous">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
            </li>
            <template v-for="pageIndex in maxPage" :key="'page_'+pageIndex">
                <li>
                    <button @click="goToPage(pageIndex)"
                        :class="{ 'mx-1 flex h-9 w-9 items-center justify-center rounded-full border border-blue-gray-100 p-0 text-sm text-gray-500 transition duration-150 ease-in-out hover:bg-gray-300': true, 'bg-blue-400': pageIndex === page, 'bg-transparent': pageIndex !== page }">
                        {{ pageIndex }}
                    </button>
                </li>
            </template>
            <li v-if="page + 1 <= maxPage">
                <button @click="goToPage(page + 1)"
                    class="mx-1 flex h-9 w-9 items-center justify-center rounded-full border border-blue-gray-100 bg-transparent p-0 text-sm text-blue-gray-500 transition duration-150 ease-in-out hover:bg-gray-300"
                    href="#" aria-label="Next">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </li>
        </ul>
    </nav>
    <table class="table-auto w-full" v-if="!isLoading">
        <thead class="">
            <tr class="[&>*]:text-center [&>*]:font-semibold text-gray-700 bg-blue-100 [&>*]:py-4">
                <template v-for="(column, index) in columnsKeys">
                    <th :class="{
                        'rounded-tl-lg': index === 0,
                        'rounded-tr-lg': index === columnsKeys.length - 1
                    }">
                        <button @click="handleOrderBy(column)">
                            {{ columns[column].label }}
                            <i :class="{
                                'fa-solid fa-arrow-up': columns[column].orderBy === 'ASC',
                                'fa-solid fa-arrow-down': columns[column].orderBy === 'DESC',
                            }"></i>
                        </button>
                    </th>
                </template>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <template v-for="(product, index) in products" :key="product.id">
                <tr :class="{ '[&>*]:py-2': true, 'bg-gray-100': index % 2 == 0 }">
                    <td>
                        <p :class="{ 'text-center font-semibold': true }">
                            {{ product.name }}
                        </p>
                    </td>
                    <td>
                        <p :class="{ 'text-center font-semibold': true }">
                            {{ product.category.label }}
                        </p>
                    </td>
                    <td>
                        <p :class="{ 'text-center font-semibold': true }">
                            {{ product.productReferences.length }}
                        </p>
                    </td>
                    <td class="text-center">
                        {{ new Date(product.createdAt.timestamp * 1000).toLocaleString('fr-FR', {
                            day: '2-digit', month:
                                '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                    </td>
                    <td class="flex">
                        <button
                            class="flex-1 bg-blue-200 hover:bg-blue-300 text-white text-center rounded-md px-2 py-1 mx-5"
                            @click="selectedProduct = product">
                            <i class="fa-solid fa-magnifying-glass">
                            </i>
                        </button>
                        <button @click="updateIsFavoriteState(product)"
                            class="flex-1 bg-yellow-400 hover:bg-yellow-500 text-white text-center rounded-md px-2 py-1 mx-5">
                            <i class="fa-heart fa-solid" :class="{
                                'text-red-700': product.isFavorite
                            }">
                            </i>
                        </button>
                        <button @click="toggleSoftDelete(product)"
                            class="flex-1 text-white text-center rounded-md px-2 py-1 mx-5" :class="{
                                'bg-red-900 hover:bg-red-950': product.deletedAt === null,
                                'bg-lime-600 hover:bg-lime-700': product.deletedAt !== null
                            }">
                            <i class="fa-trash fa-solid" v-if="product.deletedAt === null">
                            </i>
                            <i class="fa-solid fa-trash-arrow-up" v-else></i>
                        </button>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
    <loadingSpinner v-else></loadingSpinner>

</template>

<script>
import axios from 'axios';

import popup from "./popup.vue";
import Spinner from "../../components/Spinner.vue";
import Toast from '../../components/Toast.vue';

export default {
    props: {
        apiToken: {
            required: true,
            type: String
        },
        categories: {
            required: true,
            type: String
        }
    },
    components: {
        'popup': popup,
        'loadingSpinner': Spinner,
        'toast': Toast
    },
    data() {
        return {
            page: 1,
            maxPage: null,
            resultCount: null,
            isLoading: false,
            selectedState: 0,
            stateOptions: {
                all: { label: 'Tous', value: 0 },
                deleted: { label: 'Supprimés', value: -1 },
                active: { label: 'Actifs', value: 1 }

            },
            feedback: {
                msg: null,
                type: null,
            },
            columns: {
                name: {
                    label: 'Nom du produit',
                },
                category: {
                    label: 'Catégorie',
                },
                references: {
                    label: 'Tarifs disponibles',
                    orderBy: null,
                },
                createdAt: {
                    label: 'Crée le',
                    orderBy: null,
                },
                action: {
                    label: '',
                    orderBy: null
                },
            },
            products: [],
            productCategories: [],
            queryInput: "",
            queryDebounceTimeout: null,
            orderByDebounceTimeout: null,
            selectedCategory: null,
            selectedProduct: null
        }
    },
    computed: {
        columnsKeys() {
            return Object.keys(this.columns);
        },
        columnsToUrl() {
            return Object.values(this.columns).reduce((urlArgs, column, index) => urlArgs + (column.orderBy !== null && typeof column.orderBy !== 'undefined'
                ? '&orderBy' + Object.keys(this.columns)[index].charAt(0) + Object.keys(this.columns)[index].slice(1) + '=' + column.orderBy
                : '')
                , '')
        }
    },
    methods: {
        handleOrderBy(columnName) {
            let nextState = function (state) {
                switch (state) {
                    case null:
                        return "ASC";
                    case "ASC":
                        return "DESC";
                    case "DESC":
                        return null;
                }
            }

            // On ne sort pas ces columns
            if (['action'].includes(columnName))
                return;

            this.columns[columnName].orderBy = nextState(this.columns[columnName].orderBy);
            this.debouncedOrderByInputHandler();
        },
        debouncedProductInputHandler() {
            clearTimeout(this.queryDebounceTimeout);
            this.queryDebounceTimeout = setTimeout(() => {
                this.page = 1;
                this.fetchPaginatedProduct();
            }, 0.8 * 1000);
        },
        debouncedOrderByInputHandler() {
            clearTimeout(this.orderByDebounceTimeout);
            this.orderByDebounceTimeout = setTimeout(() => {
                this.page = 1;
                this.fetchPaginatedProduct();
            }, 0.5 * 1000);
        },
        goToPage(nextPage = 1) {
            if (nextPage < 1 || nextPage > this.maxPage)
                //TODO : Ajouter un toast de feedback
                return;
            this.page = nextPage;
            this.fetchPaginatedProduct()

        },
        fetchPaginatedProduct() {
            this.isLoading = true;
            let paramString = `?page=${this.page}&deleted=${this.selectedState}`;

            if (this.queryInput !== "") {
                paramString += "&query=" + this.queryInput;
            }

            if (this.selectedCategory !== null) {
                paramString += "&cat=" + this.selectedCategory;
            }

            if (this.columnsToUrl.length !== 0) {
                paramString += this.columnsToUrl;
            }

            axios.get(`/api/admin/produits${paramString}`, {
                headers: {
                    "Authorization": `Bearer ${this.apiToken}`
                }
            })
                .then((res) => {
                    this.page = res.data.page;
                    this.maxPage = res.data.totalPage;
                    this.resultCount = res.data.nbResult;
                    this.products = res.data.products;
                    this.isLoading = false;
                });
        },
        handleProductReferenceDelete(payload) {
            let index = this.products.findIndex((product) => product.id === this.selectedProduct.id);
            if (index !== -1) {
                this.products[index].productReferences = payload;
            }
        },
        handleProductSave(payload) {
            let index = this.products.findIndex((product) => product.id === this.selectedProduct.id);
            if (index !== -1) {
                this.products[index] = payload;
            }
        },
        toastFeedback(payloadToast) {
            this.feedback.msg = payloadToast.messages;
            this.feedback.type = payloadToast.type;
        },
        updateIsFavoriteState(targetedProduct) {
            let index = this.products.findIndex((product) => product.id === targetedProduct.id);
            if (index === -1) {
                this.toastFeedback({
                    messages: `Le produit ${targetedProduct.name} n'est pas trouvable veuiller réessayer`,
                    type: "danger"
                })
                return;
            }
            axios.get(`/api/admin/produits/${targetedProduct.id}/favoris`, {
                headers: {
                    "Authorization": `Bearer ${this.apiToken}`
                }
            })
                .then((res) => {
                    this.products[index] = res.data;
                    this.toastFeedback({
                        messages: `Le produit ${targetedProduct.name} est désormais en ${this.products[index].isFavorite ? '' : 'non'} favoris`,
                        type: this.products[index].isFavorite ? 'success' : 'warning'
                    })
                })
                .catch((err) => {
                    this.toastFeedback({
                        messages: err.response.data.error.msg.map((err) => err.message),
                        type: "danger"
                    })
                });
        },
        toggleSoftDelete(targetedProduct) {
            let index = this.products.findIndex((product) => product.id === targetedProduct.id);
            if (index === -1) {
                this.toastFeedback({
                    messages: `Le produit ${targetedProduct.name} n'est pas trouvable veuiller réessayer`,
                    type: "danger"
                })
                return;
            }
            axios.delete(`/api/admin/produits/${targetedProduct.id}/suppression`, {
                headers: {
                    "Authorization": `Bearer ${this.apiToken}`
                }
            })
                .then((res) => {
                    this.products[index] = res.data;
                    this.toastFeedback({
                        messages: `Le produit ${targetedProduct.name} est désormais en ${this.products[index].deletedAt !== null ? 'supprimé' : 'actif'}`,
                        type: this.products[index].deletedAt !== null ? 'warning' : 'success'
                    })
                })
                .catch((err) => this.toastFeedback({
                    messages: err.response.data.error.msg.map((err) => err.message),
                    type: "danger"
                }));
        },
        resetPage() {
            this.page = 1;
        },
        
    },
    watch: {
        selectedState(newState, oldState) {
            if (newState !== oldState) {
                this.resetPage();
            }
        },
        selectedCategory(newCategory, oldCateogry) {
            if (newCategory !== oldCateogry) {
                this.resetPage();
            }
        }

    },
    mounted() {
        this.fetchPaginatedProduct();
        this.productCategories = JSON.parse(this.categories);
    }
}
</script>