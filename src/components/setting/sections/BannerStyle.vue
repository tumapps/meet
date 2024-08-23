<template>
  <div>
    <h6 class="mt-4 mb-3">Banner Style</h6>
    <div class="d-grid gap-3 grid-cols-3 mb-4">
      <radio-input btn-name="header_banner" :img-component="true" labelClass="p-0 d-block overflow-hidden" label-title="Color" class-name="text-center" id="header-banner-color" :default-checked="headerbanner" value="navs-bg-color" @onChange="updateRadio">
        <img src="@/assets/images/settings/dark/11.png" alt="hover" class="mode dark-img img-fluid" width="200" height="200" loading="lazy" />
        <img src="@/assets/images/settings/light/11.png" alt="hover" class="mode light-img img-fluid" width="200" height="200" loading="lazy" />
      </radio-input>
      <radio-input btn-name="header_banner" :img-component="true" labelClass="p-0 d-block overflow-hidden" label-title="Image" class-name="text-center" id="header-banner-default" :default-checked="headerbanner" value="default" @onChange="updateRadio">
        <img src="@/assets/images/settings/dark/04.png" alt="hover" class="mode dark-img img-fluid" width="200" height="200" loading="lazy" />
        <img src="@/assets/images/settings/light/04.png" alt="hover" class="mode light-img img-fluid" width="200" height="200" loading="lazy" />
      </radio-input>
      <radio-input btn-name="header_banner" :img-component="true" labelClass="p-0 d-block overflow-hidden" label-title="Hide" class-name="text-center" id="hide-banner" :default-checked="headerbanner" value="hide" @onChange="updateRadio">
        <img src="@/assets/images/settings/dark/05.png" alt="boxed" class="mode dark-img img-fluid" width="200" height="200" loading="lazy" />
        <img src="@/assets/images/settings/light/05.png" alt="boxed" class="mode light-img img-fluid" width="200" height="200" loading="lazy" />
      </radio-input>
    </div>
  </div>
</template>
<script>
import { computed } from 'vue'

// Pinia Store
import { useSetting } from '@/store/pinia'

import RadioInput from '@/components/custom/elements/RadioInput'
export default {
  name: 'BannerStyle',
  components: {
    RadioInput
  },
  setup() {
    const store = useSetting()
    const headerbanner = computed(() => store.header_banner_value)
    const updateRadio = (value, name) => {
      store[name](value)
    }
    return {
      headerbanner,
      updateRadio
    }
  }
}
</script>
