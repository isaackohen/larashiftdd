<template>
    <div class="loaderContainer">
			<template v-if="theme === 'dark'">
				<img style="height: 45px;" src="https://games.cdn4.dk/loading.gif">
			</template>
			<template v-else>
				<img style="height: 45px;" src="/img/misc/loader-white.gif">
			</template>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
	export default {
        computed: {
            ...mapGetters(['theme'])
        },
        data() {
            return {
                expand: false
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";


</style>
