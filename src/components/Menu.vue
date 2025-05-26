<script setup lang="ts">
import MenuButton from '@/components/Menu/MenuButton.vue'
import MenuCopyright from '@/components/Menu/MenuCopyright.vue'
import MenuSettings from '@/components/Menu/MenuSettings.vue'
import Drawer from 'primevue/drawer'
import { ref } from 'vue'

const visible = ref<boolean>(false)
</script>

<template>
  <div class="menu" :class="{ active: visible }">
    <Drawer
      class="menu-drawer"
      v-model:visible="visible"
      header="Settings"
      :show-close-icon="false"
    >
      <MenuSettings />

      <template #footer>
        <MenuCopyright />
      </template>
    </Drawer>
    <MenuButton class="menu__button" :active="visible" @click="visible = !visible" />
  </div>
</template>

<style>
.menu-drawer {
  --p-drawer-header-padding: 0.5rem 1.25rem;
  --p-drawer-content-padding: 1.25rem;
  --p-drawer-footer-padding: 0;
}
</style>
<style scoped>
.menu__button {
  left: 0.75rem;
  position: absolute;
  top: 0.5rem;
  transition: left 0.3s;
  z-index: 1102;
}
.menu.active .menu__button {
  left: calc(20rem - 40px - 0.75rem);
}
</style>
