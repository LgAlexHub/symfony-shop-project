<template>
    <div class="grid grid-cols-4 gap-4">
        <p class="text-center bg-gray-800 rounded font-semibold text-white">Nom client</p>
        <p class="text-center bg-gray-800 rounded font-semibold text-white">Email client</p>
        <p class="text-center bg-gray-800 rounded font-semibold text-white">Prix du panier</p>
        <p class="text-center bg-gray-800 rounded font-semibold text-white">Statut de la commande</p>
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
            .then((res) => console.log(res));
        }
    },
    mounted(){
        this.fetchPaginatedOrder();
    }
}
</script>