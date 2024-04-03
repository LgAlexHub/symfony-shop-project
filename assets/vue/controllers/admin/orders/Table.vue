<template>
    <popup v-if="selectedOrder !== null" :order="selectedOrder" :api-token="apiToken" @close-popup="selectedOrder = null"></popup>
    <div class="pt-2 relative mx-auto text-gray-600">
        <input v-model="queryInput" @input="debouncedProductInputHandler" class="border-2 border-gray-300 bg-white w-full h-10 px-5 pr-16 rounded-lg text-sm focus:border-black"
          type="search" name="search" placeholder="Recherche par nom, prénom, email">
        <button type="submit" class="absolute right-0 top-0 mt-4 mr-4">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>

    </div>
    <nav class="my-12">
        <ul class="flex justify-center">
            <li v-if="page > 1">
                <button @click="goToPage(page - 1)" class="mx-1 flex h-9 w-9 items-center justify-center rounded-full border border-blue-gray-100 bg-transparent p-0 text-sm text-blue-gray-500 transition duration-150 ease-in-out hover:bg-gray-300" href="#" aria-label="Previous">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
            </li>
            <template v-for="pageIndex in maxPage" :key="'page_'+pageIndex">
                <li>
                    <button 
                        @click="goToPage(pageIndex)" 
                        :class="{'mx-1 flex h-9 w-9 items-center justify-center rounded-full border border-blue-gray-100 p-0 text-sm text-gray-500 transition duration-150 ease-in-out hover:bg-gray-300' : true, 'bg-blue-400' : pageIndex === page, 'bg-transparent' : pageIndex !== page}"
                    >
                        {{ pageIndex }}
                    </button>
                </li>
            </template>
            <!-- <li>
                <button class="mx-1 flex h-9 w-9 items-center justify-center rounded-full border border-blue-gray-100 bg-blue-300 p-0 text-sm text-blue-gray-500 transition duration-150 ease-in-out hover:bg-gray-300">{{ page }}</button>
            </li> -->
            <li v-if="page + 1 <= maxPage">
                <button @click="goToPage(page + 1)" class="mx-1 flex h-9 w-9 items-center justify-center rounded-full border border-blue-gray-100 bg-transparent p-0 text-sm text-blue-gray-500 transition duration-150 ease-in-out hover:bg-gray-300" href="#" aria-label="Next">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </li>
        </ul>
    </nav>
    <table class="table-auto divide-y divide-gray-200 w-full">
        <thead class="">
            <tr class="[&>*]:text-center [&>*]:font-semibold text-gray-700 bg-blue-100 [&>*]:py-4">
                <template v-for="(column, index) in columnsKeys">
                    <th :class="{
                        'rounded-tl-lg' : index === 0,
                        'rounded-tr-lg' : index === columnsKeys.length - 1
                    }">
                        <button @click="handleOrderBy(column)">
                            {{ columns[column].label }}
                            <i :class="{
                                'fa-solid fa-arrow-up' : columns[column].orderBy === 'ASC',
                                'fa-solid fa-arrow-down' : columns[column].orderBy === 'DESC',
                            }"></i>
                        </button>
                    </th>
                </template>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <template v-for="(order, index) in orders" :key="order.serializeUuid">
                <tr :class="{ '[&>*]:py-2': true, 'bg-gray-100' : index % 2 == 0 }">
                    <td>
                        <p :class="{ 'text-center font-semibold': true }">
                            {{ order.clientFirstName }}
                        </p>
                    </td>
                    <td>
                        <p :class="{ 'text-center font-semibold': true }">
                            {{ order.email }}
                        </p>
                    </td>
                    <td>
                        <p :class="{ 'text-center font-semibold': true }">
                            {{ (order.totalPrice / 100).toFixed(2).replace('.', ',') }} €
                        </p>
                    </td>
                    <td>
                        <p :class="{ 'text-center font-semibold' : true, 'text-green-500' : order.isDone, 'text-red-500' : !order.isDone }">
                            {{ order.isDone ? 'Terminée' : 'En cours' }}
                        </p>
                    </td>
                    <td class="text-center">
                        {{ new Date(order.createdAt.timestamp * 1000).toLocaleString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                    </td>
                    <td>
                        <button class="w-10/12 bg-blue-200 hover:bg-blue-300 text-white text-center rounded-md px-2 py-1 w-100 mx-5" @click="selectedOrder = order">
                            <i class="fa-solid fa-magnifying-glass">
                            </i>
                        </button>    
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</template>

<script>
import axios from 'axios';
import popup from "./popup.vue";

export default {
    props : {
        apiToken : {
            required : true,
            type : String
        }
    },
    components : {
        'popup' : popup
    },
    data(){
        return {
            page : 1,
            maxPage : null,
            resultCount : null,
            columns : {
                clientLastName : {
                    label : 'Nom client',
                },
                email : {
                    label : 'Email',
                },
                totalPrice: {
                    label : 'Prix total du panier',
                    orderBy : null,
                },
                isDone: {
                    label : 'Statut de la commande',
                    orderBy : null,
                },
                createdAt: {
                    label : 'Commandé le',
                    orderBy : null,
                },
                action :{
                    label : '',
                    orderBy : null
                },
            },
            orders : [],
            selectedOrder : null,
            queryInput: "",
            queryDebounceTimeout : null,
            orderByDebounceTimeout : null,

        }
    }, 
    computed : {
        columnsKeys(){
            return Object.keys(this.columns);
        },
        columnsToUrl(){
            return Object.values(this.columns).reduce((urlArgs, column, index) => urlArgs + (column.orderBy !== null && typeof column.orderBy !== 'undefined'
                ? '&orderBy' + Object.keys(this.columns)[index].charAt(0) + Object.keys(this.columns)[index].slice(1) + '=' + column.orderBy 
                : '')
            , '')
        }
    },
    methods : {
        handleOrderBy(columnName) {
            let nextState = function(state) { 
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
            if(['action', 'clientFirstName', 'clientLastName'].includes(columnName))
                return;

            this.columns[columnName].orderBy = nextState(this.columns[columnName].orderBy);
            this.debouncedOrderByInputHandler();
        },
        debouncedProductInputHandler() {
        // to bind on input text
            clearTimeout(this.queryDebounceTimeout);
            this.queryDebounceTimeout = setTimeout(() => {
                this.page = 1;
                this.fetchPaginatedOrder();
            }, 0.8 * 1000);
        },
        debouncedOrderByInputHandler() {
        // to bind on input text
            clearTimeout(this.orderByDebounceTimeout);
            this.orderByDebounceTimeout = setTimeout(() => {
                this.page = 1;
                this.fetchPaginatedOrder();
            }, 0.5 * 1000);
        },
        goToPage(nextPage = 1){
            if(nextPage < 1 || nextPage > this.maxPage)
                //TODO : Ajouter un toast de feedback
                return;
            this.page = nextPage;
            this.fetchPaginatedOrder()

        },
        fetchPaginatedOrder(){
            let paramString = "?page="+this.page;

            if (this.queryInput !== ""){
                paramString+="&query="+this.queryInput;
            }
            
            if(this.columnsToUrl.length !== 0){
                paramString+=this.columnsToUrl;
            }

            axios.get(`/api/admin/commandes${paramString}`, {
                headers : {
                    "Authorization" : `Bearer ${this.apiToken}`
                }
            })
            .then((res) => {
                this.page = res.data.page;
                this.maxPage = res.data.totalPage;
                this.resultCount = res.data.nbResult;
                this.orders = res.data.orders
            });
        }
    },
    mounted(){
        this.fetchPaginatedOrder();
    }
}
</script>