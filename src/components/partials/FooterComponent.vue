<template>
  <!-- Footer Section Start -->
  <footer :class="`footer ${footerStyle}`">
    <div class="footer-body">
      <div class="right-panel">
        © {{ new Date().getFullYear() }} <a href="https://crackit.co.ke/" target="_blank">CrackIT Technologies. </a>All Rights Reserved.
      </div>
    </div>
  </footer>
  <!-- Footer Section End -->
   <b-offcanvas placement="bottom" title="Share">
    <share-offcanvas></share-offcanvas>
  </b-offcanvas>
</template>

<script>
import { computed } from 'vue'
import ShareOffcanvas from '@/components/widgets/ShareOffcanvasNew.vue'

// Pinia Store
import { useSetting } from '@/store/pinia'

export default {
  components: { ShareOffcanvas },
  setup() {
    const store = useSetting()
    const footerStyle = computed(() => store.footer_style_value)

    return {
      footerStyle
    }
  },
  data() {
    return {}
  }
}
</script>
