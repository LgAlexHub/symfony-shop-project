<template>
    <div v-if="render" class="max-w-xs text-sm text-white rounded-md shadow-lg mb-3 ml-3 ml-3 z-50 absolute bottom-0 left-1/2 transform -translate-x-1/2 animate-fade" 
        role="alert"
        :class="{
           'bg-gray-800 dark:bg-gray-900' : toastType === null,
           'bg-red-800 dark:bg-red-900' : toastType === 'danger',
           'bg-lime-800 dark:bg-lime-900' : toastType === 'success',
           'bg-yellow-800 dark:bg-yellow-900' : toastType === 'warning'
        }"
    >
        <div class="flex px-4 py-2 mx-4">
            <template v-if="messagesIsArray">
                <ul>
                    <li v-for="msg in toastMessages">
                        {{ msg }}
                    </li>
                </ul>
            </template>
            <template v-else>
                <p>
                    {{ toastMessages }}
                </p>
            </template>
            <div class="ml-auto">
                <button type="button" @click="render = false"
                    class="inline-flex flex-shrink-0 justify-center items-center h-4 w-4 rounded-md text-white/[.5] hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-gray-600 transition-all text-sm dark:focus:ring-offset-gray-900 dark:focus:ring-gray-800">
                    <span class="sr-only">Close</span>
                    <svg class="w-3.5 h-3.5" width="16" height="16" viewBox="0 0 16 16" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M0.92524 0.687069C1.126 0.486219 1.39823 0.373377 1.68209 0.373377C1.96597 0.373377 2.2382 0.486219 2.43894 0.687069L8.10514 6.35813L13.7714 0.687069C13.8701 0.584748 13.9882 0.503105 14.1188 0.446962C14.2494 0.39082 14.3899 0.361248 14.5321 0.360026C14.6742 0.358783 14.8151 0.38589 14.9468 0.439762C15.0782 0.493633 15.1977 0.573197 15.2983 0.673783C15.3987 0.774389 15.4784 0.894026 15.5321 1.02568C15.5859 1.15736 15.6131 1.29845 15.6118 1.44071C15.6105 1.58297 15.5809 1.72357 15.5248 1.85428C15.4688 1.98499 15.3872 2.10324 15.2851 2.20206L9.61883 7.87312L15.2851 13.5441C15.4801 13.7462 15.588 14.0168 15.5854 14.2977C15.5831 14.5787 15.4705 14.8474 15.272 15.046C15.0735 15.2449 14.805 15.3574 14.5244 15.3599C14.2437 15.3623 13.9733 15.2543 13.7714 15.0591L8.10514 9.38812L2.43894 15.0591C2.23704 15.2543 1.96663 15.3623 1.68594 15.3599C1.40526 15.3574 1.13677 15.2449 0.938279 15.046C0.739807 14.8474 0.627232 14.5787 0.624791 14.2977C0.62235 14.0168 0.730236 13.7462 0.92524 13.5441L6.59144 7.87312L0.92524 2.20206C0.724562 2.00115 0.611816 1.72867 0.611816 1.44457C0.611816 1.16047 0.724562 0.887983 0.92524 0.687069Z"
                            fill="currentColor" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props : {
            dataMessages : {
                required: false, 
                type : [String, Array]
            },
            dataType  : {
                required: false,
                type : String
            }
        },
        data(){
            return {
                messagesIsArray : null,
                toastMessages : null,
                toastType : null,
                render : false,
            }
        },
        computed : {
            isToastReadyToRender(){
                return this.toastMessages !== null && this.toastType !== null; 
            }
        },
        methods: {
            toggle(){
                if (this.isToastReadyToRender){
                    this.render = true;
                    setTimeout(() => {
                        this.render = false;
                    }, 3000);
                }
            },
        },
        mounted(){
            this.messagesIsArray = Array.isArray(this.dataMessages);
            this.toastMessages = this.dataMessages;
            this.toastType = this.dataType;
            this.toggle();
        },
        watch: {
            dataMessages(newProps, oldProps){
                this.messagesIsArray = Array.isArray(newProps);
                this.toastMessages = newProps;
                this.toggle();
            },
            dataType(newProps, oldProps){
                this.toastType = newProps;
                this.toggle();
            }
        }
    }
</script>