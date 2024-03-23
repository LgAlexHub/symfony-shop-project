<template>
    <popup v-if="selectedOrder !== null" :order="selectedOrder" :api-token="apiToken" @close-popup="selectedOrder = null"></popup>
    <nav class="my-12">
        <ul class="flex justify-center">
            <li v-if="page > 1">
                <button @click="goToPage(page - 1)" class="mx-1 flex h-9 w-9 items-center justify-center rounded-full border border-blue-gray-100 bg-transparent p-0 text-sm text-blue-gray-500 transition duration-150 ease-in-out hover:bg-gray-300" href="#" aria-label="Previous">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
            </li>
            <li v-if="page > 1">
                <button @click="goToPage(page - 1)" class="mx-1 flex h-9 w-9 items-center justify-center rounded-full border border-blue-gray-100 bg-transparent p-0 text-sm text-blue-gray-500 transition duration-150 ease-in-out hover:bg-gray-300" href="#">{{ page - 1 }}</button>
            </li>
            <li>
                <button class="mx-1 flex h-9 w-9 items-center justify-center rounded-full border border-blue-gray-100 bg-blue-300 p-0 text-sm text-blue-gray-500 transition duration-150 ease-in-out hover:bg-gray-300" href="#">{{ page }}</button>
            </li>
            <li v-if="page + 1 <= maxPage">
                <button @click="goToPage(page + 1)" class="mx-1 flex h-9 w-9 items-center justify-center rounded-full border border-blue-gray-100 bg-transparent p-0 text-sm text-blue-gray-500 transition duration-150 ease-in-out hover:bg-gray-300" href="#">{{ page + 1 }}</button>
            </li>
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
                <th class="rounded-tl-lg">Nom client</th>
                <th>Email client</th>
                <th>Prix du panier</th>
                <th>Statut de la commande</th>
                <th>Commandé le</th>
                <th class="rounded-tr-lg"></th>
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
            orders : [],
            selectedOrder : null
        }
    }, 
    methods : {
        goToPage(nextPage = 1){
            if(nextPage < 1 || nextPage > this.maxPage)
                //TODO : Ajouter un toast de feedback
                return;
            this.page = nextPage;
            this.fetchPaginatedOrder()

        },
        fetchPaginatedOrder(){
            let paramString = "?page="+this.page
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