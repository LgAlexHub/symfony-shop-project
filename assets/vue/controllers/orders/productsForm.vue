<template>
  <toast v-if="canBeRender" @popup-toggle="onPopupDisappear()" :propsMessage="feedbackMessage"
    :propsType="feedbackType"></toast>
  <div class="grid grid-cols-5 gap-5 my-2">
    <div class="px-5 col-span-2 h-100 overflow-scroll">
      <p class="text-xl">Votre panier</p>
      <template v-for="orderItem in orderItems" :key="orderItem.item.slug">
        <div class="border-2 my-1 border-solid border-lime-950 rounded px-2 py-1 grid grid-cols-3">
          <div class="grid grid-rows-2 col-span-2">
            <p class="text-md">
              {{ orderItem.item.product.name }} - {{ orderItem.item.weight }}
              {{ orderItem.item.weightType }}
            </p>
            <div class="grid grid-cols-3">
              <p class="">{{ (orderItem.item.price / 100).toFixed(2) }} €</p>
              <div class="inline col-span-2 place-self-center py-1">
                <button class="px-2 border-2 border-solid border-lime-950 rounded"
                  @click="editQuantity(orderItem.item.slug)">
                  <span class="">-</span>
                </button>
                <span class="px-2 mx-1 border-2 border-solid border-lime-950 rounded">{{ orderItem.quantity }}</span>
                <button class="px-2 border-2 border-solid border-lime-950 rounded"
                  @click="editQuantity(orderItem.item.slug, true)">
                  +
                </button>
              </div>
            </div>
          </div>
          <div>
            <button
              class="border-2 bg-red-600 text-white hover:bg-red-900 text-xs border-solid border-lime-950 px-2 py-1 w-full h-1/2 my-5"
              @click="removeItem(orderItem.item.slug)">
              Suppr.
            </button>
          </div>
        </div>
      </template>
    </div>
    <div class="col-span-3 px-2">
      <div class="border-2 border-solid border-lime-950 px-2 rounded-sm py-1 w-full">
        <products-fetcher @product-added="onProductAdded"></products-fetcher>
      </div>
    </div>
  </div>
  <div>
    <a class="border border-blue-400 rounded-md bg-sky-400 px-2 py-1 text-white hover:bg-blue-800 hover:border-transparent" :href="'/commandes/'+orderUuid+'/valider'">
      Valider la commande
    </a>
  </div>
</template>

<script>
import axios from "axios";
import ProductsFetcher from "./ProductsFetcher.vue";
import toast from "../components/Toast.vue";

export default {
  name: 'products-form',
  components: {
    'products-fetcher': ProductsFetcher,
    'toast': toast
  },
  props: {
    orderUuid: {
      type: String,
      required: true,
    },
    prefetchOrderItem: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      orderItems: [],
      feedbackMessage: null,
      feedbackType: null,
    };
  },
  methods: {
    removeItem(slug){
      let itemIndex = this.findItemIndexBySlug(slug);
      if (itemIndex === -1){
        this.setToastMessage(`Erreur : ${slug} introuvable` , 'danger');
        return;
      }
      this.orderItems.splice(itemIndex, 1);
      this.persistChangeInDatabase('delete', slug);
    },
    /**
     * action type bool , true for addition , false sub
     */
    editQuantity(slug, actionType) {
      let itemIndex = this.findItemIndexBySlug(slug);
      if (itemIndex === -1) {
        this.setToastMessage("Erreur : produit dans le panier introuvable !", "danger");
        return;
      }

      // addition
      if (actionType) {
        this.orderItems[itemIndex].quantity += 1;
        this.persistChangeInDatabase('addition', slug);
      }

      // soustraction
      if (!actionType) {
        if (this.orderItems[itemIndex].quantity < 2) {
          this.orderItems.splice(itemIndex, 1);
        } else {
          this.orderItems[itemIndex].quantity -= 1
        }
        this.persistChangeInDatabase('substraction', slug);
      }
    },
    findItemIndexBySlug(slug) {
      return this.orderItems.findIndex((element) => element.item.slug === slug);
    },
    onProductAdded(payloadItem) {
      let itemIndex = this.findItemIndexBySlug(payloadItem.slug);
      if (itemIndex === -1) {
        itemIndex = this.orderItems.length;
        this.orderItems[itemIndex] = {
          quantity: 1,
          item: {
            price: payloadItem.price,
            product: {
              name: payloadItem.product.name,
              slug: payloadItem.product.slug,
            },
            slug: payloadItem.slug,
            weight: payloadItem.weight,
            weightType: payloadItem.weightType,
          },
        }
        this.persistChangeInDatabase('addition', payloadItem.slug);
      }
    },
    persistChangeInDatabase(action, slug) {
      let apiEndPoint = null;
      switch (action) {
        case 'addition':
          apiEndPoint = 'ajouter-produit'
          break;
        case 'substraction':
          apiEndPoint = 'diminuer-produit'
          break;
        case 'delete':
          apiEndPoint = 'supprimer-produit'
      }

      if (slug === null || apiEndPoint === null) {
        // ajouter un feedback et dire pourquoi on envoie rien au serveur
        return;
      }
      let bodyFormData = new FormData();
      bodyFormData.append("form[productReferenceSlug]", slug);
      axios
        .post(`/api/commandes/${this.orderUuid}/${apiEndPoint}`, bodyFormData)
        .then((res) => {
          this.setToastMessage('Panier sauvegardé avec succès !', 'success')
        })
        .catch((err) => this.setToastMessage(err.message, 'danger'));
    },
    setToastMessage(message, type) {
      this.feedbackMessage = message;
      this.feedbackType = type;
    },
    onPopupDisappear() {
      this.feedbackMessage = null;
      this.feedbackType = null;
    }
  },
  computed: {
    canBeRender() {
      return this.feedbackMessage !== null && this.feedbackType !== null;
    },
  },
  mounted() {
    this.orderItems = JSON.parse(this.prefetchOrderItem);
    // vm.$forceUpdate();
  },
};
</script>