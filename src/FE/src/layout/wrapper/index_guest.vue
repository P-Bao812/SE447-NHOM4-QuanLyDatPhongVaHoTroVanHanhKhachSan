<template>
  <headerguest></headerguest>
  <router-view></router-view>
  <footerguest class="text-boot"></footerguest>
</template>
<script>
import Headerguest from '../components/guest/Headerguest.vue';
import Footerguest from '../components/guest/Footerguest.vue';
export default {
  components: {
    Headerguest,
    Footerguest
  }
}
</script>
<style></style>
