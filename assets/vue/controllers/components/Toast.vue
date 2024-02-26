<template>
    <div :class="toastClass">
        <p  v-if="message !== null && message !== ''" class="text-center text-neutral-50">
            {{ message }}
        </p>
        <button @click="toggle">test</button>
    </div>
</template>

<script>
export default {
    name : "toast",
    props : {
        propsMessage : {
            type : String,
            required : true,
        },
        propsType : {
            type : String, 
            required : false
        }
    }, 
    data() {
        return {
            message : "gzezfze",
            displayType : "success",
            durationInSecond : 5,
            visibility : false,
            types : {
                'success' : ['border-lime-100', 'from-lime-800', 'to-green-500'],
                'danger'  : ['border-red-100', 'from-red-800', 'to-amber-400'],
                'default' : ['border-sky-400', 'bg-blue-300', 'bg-opacity-70']
            },
            defaultClasses : ['z-4', 'ease-in', 'duration-700', 'transition-opacity', 'absolute', 'bottom-0', 'left-1/2', 'transform', '-translate-x-1/2', 'w-32', 'border-2', 'rounded', 'px-2', 'py-1', 'mb-2']
        };
    },
    computed: {
            toastClass() {
                let cssClasses = this.defaultClasses;
                if(this.type !== null && this.types.hasOwnProperty(this.displayType)){
                    cssClasses.push('bg-gradient-to-t', ...this.types[this.displayType])
                }else{
                    cssClasses.push(...this.types[this.displayType])
                }
                // cssClasses.push(
                //     ... this.displayType !== null && this.types.hasOwnProperty(this.displayType)
                //         ? [].push('bg-gradient-to-t', ...this.types[this.displayType])
                //         : this.types[this.displayType]
                // )
                cssClasses.push(`opacity-${this.visibility ? '100' : '0'}`)
                return cssClasses.join(' ');
            },
    },
    methods: {
        toggle(){
            this.visibility = !this.visibility;
            setTimeout(() => this.visibility = !this.visibility, this.durationInSecond * 1000);
        }
    },
    mounted(){
        if(this.propsMessage){

        }
    }
};
</script>