<template>
    <div class="grid grid-cols-5 gap-4">
        <p class="text-center bg-gray-800 rounded font-semibold text-white">Nom client</p>
        <p class="text-center bg-gray-800 rounded font-semibold text-white">Email client</p>
        <p class="text-center bg-gray-800 rounded font-semibold text-white">Prix du panier</p>
        <p class="text-center bg-gray-800 rounded font-semibold text-white">Statut de la commande</p>
        <p class="text-center bg-gray-800 rounded font-semibold text-white">Action</p>
        <template v-for="(order, index) in orders" :key="order.serializeUuid">
            <p :class="{
                'text-center font-semibold': true,
                'text-gray-500' : index % 2 == 0,
            }">
                {{ order.clientFirstName }}
            </p>
            <p :class="{
                'text-center font-semibold': true,
                'text-gray-500' : index % 2 == 0,
            }">
                {{ order.email }}
            </p>
            <p :class="{
                'text-center font-semibold': true,
                'text-gray-500' : index % 2 == 0,
            }">
                {{ (order.totalPrice / 100).toFixed(2).replace('.', ',') }} €
            </p>
            <p :class="{
                'text-center font-semibold' : true,
                'text-green-500' : order.isDone,
                'text-red-500' : !order.isDone,
            }">
                {{ order.isDone ? 'Terminée' : 'En cours' }}
            </p>
            <a class="bg-blue-200 hover:bg-blue-300 text-white text-center rounded-md px-2 py-1 w-100 mx-5" :href="'/admin/commandes/' + order.id">
                <i class="fa-solid fa-magnifying-glass">
                </i>
            </a>    
            <hr><hr><hr><hr><hr>
        </template>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    props : {
        apiToken : {
            required : true,
            type : String
        }
    },
    data(){
        return {
            page : 1,
            maxPage : null,
            resultCount : null,
            orders : []
        }
    }, 
    methods : {
        fetchPaginatedOrder(){
            axios.get(`/api/admin/commandes`, {
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