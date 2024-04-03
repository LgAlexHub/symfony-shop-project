<template>
    <div v-if="editingOrder !== null" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 z-50">
        <div class="bg-white p-8 rounded-lg shadow-lg w-1/2">
            <h2 class="text-2xl font-bold mb-4">Commande - {{ editingOrder.serializeUuid }}</h2>
            <p>Idetifiant privé : <span class="font-bold">{{ editingOrder.id }}</span></p>
            <p>Prénom : <span class="font-bold">{{ editingOrder.clientFirstName }}</span></p>
            <p>Nom : <span class="font-bold">{{ editingOrder.clientLastName }}</span></p>
            <p>Email : <span class="font-bold">{{ editingOrder.email }}</span></p>
            <p>Commande créée le : <span class="font-bold"> {{ new Date(order.createdAt.timestamp * 1000).toLocaleString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</span></p>
            <p>Statut de la commande : <span :class="{ 'font-bold' : true, 'text-green-500' : editingOrder.isDone, 'text-red-500' : !editingOrder.isDone }">  {{ editingOrder.isDone ? 'Terminée' : 'En cours' }}</span></p>
            <p v-if="editingOrder.comment !== null">Commentaire : <span class="font-bold">{{ editingOrder.comment }}</span></p>
            <table class="table-fixed w-full border-separate border-spacing-1 border-2 border-slate-700 rounded mt-3 mb-10">
                <thead>
                    <tr class="[&>*]:bg-slate-600 [&>*]:border [&>*]:border-slate-300 [&>*]:text-white [&>*]:text-center px-2 py-1">
                        <td>Produit</td>
                        <td>Poids</td>
                        <td>Prix</td>
                        <td>Quantité</td>
                        <td>
                            <strong>Prix</strong><span class="text-xs"> x </span><strong>Quantité</strong>
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="orderItem in editingOrder.items" class="even:bg-white odd:bg-blue-50 [&>*]:text-center">
                        <td>
                            {{orderItem.item.product.name}}
                        </td>
                        <td>
                            {{orderItem.item.weight}} {{orderItem.item.weightType}}
                        </td>
                        <td>
                            {{(orderItem.item.price/100).toFixed(2).replace('.', ',')}} €
                        </td>
                        <td>
                            {{orderItem.quantity}}
                        </td>
                        <td> {{((orderItem.item.price/100) * orderItem.quantity).toFixed(2)}} €</td>
                    </tr>
                    <tr class="even:bg-white odd:bg-blue-50">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="text-center font-bold">Total :</td>
                        <td class="text-center">{{(editingOrder.totalPrice/100).toFixed(2)}} €</td>
                    </tr>
                </tbody>
            </table>
            <div class="grid grid-cols-2 gap-4">
                <button @click="$emit('closePopup')" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Fermer</button>
                <button 
                    @click="updateIsDoneState" 
                    :class="{
                        'mt-4 px-4 py-2 text-white rounded-md' : true,
                        'hover:bg-red-600 bg-red-500' : editingOrder.isDone,
                        'hover:bg-green-600 bg-green-500' : !editingOrder.isDone,
                    }"
                >
                    {{ editingOrder.isDone ? 'Marquer comme à faire' : 'Marquer comme faite' }}
                </button>
            </div>

        </div>
    </div>
</template>

<script>

import axios from 'axios';

export default {
    name: "popup",
    props : {
        order : {
            required : true,
            type : Object
        },
        apiToken : {
            required : true,
            type : String
        }
    },
    data() {
        return {
            editingOrder: null,
        }
    },
    methods: {
        updateIsDoneState(){
            axios.get(`/api/admin/commandes/${this.editingOrder.id}/validee`,{
                headers : {
                    "Authorization" : `Bearer ${this.apiToken}`
                }
            })
            .then((_) => this.editingOrder.isDone = !   this.editingOrder.isDone)
            .catch((err) => console.error(err));
        },
        fetchOrderProduct(){
            axios.get(`/api/admin/commandes/${this.editingOrder.id}/produits`,{
                headers : {
                    "Authorization" : `Bearer ${this.apiToken}`
                }
            })
            .then((res) => {
                this.editingOrder.items = res.data
            })
            .catch((err) => console.error(err));
        }
    },
    mounted() {
        this.editingOrder = this.order;
        this.fetchOrderProduct();
    }
}
</script>