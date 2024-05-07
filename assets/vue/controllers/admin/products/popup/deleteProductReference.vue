<template v-if="currentProductRef !== null">
    <div class="flex flex-col items-center justify-center">
        <p class="text-2xl font-semibold mb-4">
            Êtes-vous sûr de vouloir supprimer la référence : {{ currentProductRef.slug }}
        </p>
        <div class="flex">
            <button @click="$emit('onDeleteCanceled')" class="px-2 py-1 text-slate-500 hover:underline">
                Annuler
            </button>
            <button @click="onProductReferenceDeleteConfirm" class="px-2 py-1 rounded-sm bg-red-500 hover:bg-red-600 text-white">
                Supprimer
            </button>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
export default {
    name: "popupDeleteProductRef",
    props: {
        productRef: {
            required: true,
            type: Object
        },
        apiToken: {
            required: true,
            type: String
        }
    },
    emits: ["onDeleteCanceled", "onProductReferenceDeleted"],
    data() {
        return {
            currentProductRef: null,
        }
    },
    methods: {
        onProductReferenceDeleteConfirm() {
            axios.delete(`/api/admin/references/${this.currentProductRef.id}`, {
                headers: {
                    "Authorization": `Bearer ${this.apiToken}`
                }
            })
            .then((_) => {
                this.$emit("onProductReferenceDeleted", this.currentProductRef);
            }) //TODO : Ajouter un toast feedback
            .catch((error) => console.error(error)) // TODO : Ajouter un toast feedbacka
        }
    },
    beforeMount() {
        this.currentProductRef = Object.assign({}, this.productRef);
    }
}
</script>