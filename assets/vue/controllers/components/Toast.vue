<template>
    <div :class="{
        'z-4 ease-in duration-700 transition-opacity absolute bottom-0 left-1/2 transform -translate-x-1/2 w-32 border-2 rounded px-2 py-1 mb-2 bg-gradient-to-t': true,
        'border-lime-100 from-lime-800 to-green-500': this.propsType === 'success',
        'border-red-100 from-red-800 to-amber-400': this.propsType === 'danger',
        'border-sky-400 bg-blue-300 bg-opacity-70': this.propsType === 'default',
        'opacity-0': this.visibility === false,
        'opacity-100': this.visibility === true,
    }">
        <p class="text-center text-neutral-50">
            {{ propsMessage }}
        </p>
    </div>
</template>

<script>
export default {
    name: "toast",
    props: {
        propsMessage: {
            type: String,
            required: true,
        },
        propsType: {
            type: String,
            required: false
        }
    },
    data() {
        return {
            durationInSecond: 3,
            visibility: false,
        };
    },
    methods: {
        async toggle() {
            // Première pause
            await new Promise(resolve => setTimeout(resolve, 0.5 * 1000));

            // Inverse la visibilité
            this.visibility = !this.visibility;

            // Pause basée sur la durée spécifiée
            await new Promise(resolve => setTimeout(resolve, this.durationInSecond * 1000));

            // Inverse à nouveau la visibilité
            this.visibility = !this.visibility;

            // Dernière pause
            await new Promise(resolve => setTimeout(resolve, 0.5 * 1000));

            // Émet l'événement "popupToggle"
            this.$emit("popupToggle");
        },
    },
    mounted() {
        this.toggle();
    },
};
</script>