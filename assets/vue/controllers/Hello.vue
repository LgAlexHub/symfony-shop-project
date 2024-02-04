<template>
    <div @click="changeName">Hello {{ compName }}!</div>
</template>

<script>
    export default {
        props : {
            name: String
        },
        data() {
            return {
                compName : "",

            }
            
        },
        methods: {
            changeName(){
                this.compName = "Super Aléki";
            }
        },  
        mounted(){
            this.compName = this.name;
        }
    }
</script>
