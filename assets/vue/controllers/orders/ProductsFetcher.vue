<template>
  <div>
    <input
      v-model="productQueryString"
      @input="debouncedProductInputHandler"
      class="h-6 w-full text-center border-2 border-solid border-lime-950 px-2 py-1"
      type="text"
      placeholder="Recherche de produit ici..."
    />
    <div class="grid grid-cols-5 gap-3 mt-2 h-80 overflow-scroll">
      <div
        v-for="reference in fetchedProducts"
        :key="reference.id"
        @click="$emit('product-added', reference)"
        class="border-2 hover:bg-slate-300 border-solid border-lime-950 px-2 py-1"
      >
        <div class="w-full flex justify-center">
          <img class="rounded w-3/4" :src="reference.image" alt="" />
        </div>
        <p class="text-center">
          {{ reference.product.name }}
        </p>
        <p class="text-xs text-center">
          {{ reference.weight }} {{ reference.weightType }}
        </p>
      </div>
    </div>
  </div>
</template>
<script>
import axios from "axios";
export default {
  data() {
    return {
      productQueryString: "",
      fetchedProducts : [],
      debounceTimeout : null,
    };
  },
  methods: {
    debouncedProductInputHandler() {
      // to bind on input text
      clearTimeout(this.debounceTimeout);
      this.debounceTimeout = setTimeout(() => {
        this.fetchProductsByQuery(this.productQueryString);
      }, 0.5 * 1000);
    },
    fetchProductsByQuery(query) {
      axios
        .get(`/api/produits/recherche?nom=${query}`)
        .then((res) => {
          this.fetchedProducts = res.data.map((reference) => {
            return {
              price: reference.price,
              weight: reference.weight,
              weightType: reference.weightType,
              image: reference.imageUrl,
              slug: reference.slug,
              product: {
                name: reference.product.name,
                slug: reference.product.slug,
              },
            };
          })
        })
        .catch((err) => {
          console.error(err);
          // throw client feedback
        });
    },
  },
};
</script>