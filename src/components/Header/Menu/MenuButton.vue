<script setup lang="ts">
import Button from 'primevue/button'

defineProps<{
  active: boolean
}>()
</script>

<template>
  <Button
    class="menu-button"
    :class="{ active }"
    title="Menu"
    icon="pi"
    variant="text"
    severity="contrast"
  >
    <svg
      class="menu-button__icon"
      viewBox="0 0 80 80"
      fill="none"
      stroke-linecap="round"
      aria-hidden="true"
    >
      <path d="M8 20 h64" />
      <path d="M8 40 h64" />
      <path d="M8 60 h64" />
    </svg>
  </Button>
</template>

<style scoped>
.menu-button {
  --p-button-padding-y: 0.25rem;
}

.menu-button__icon {
  height: 30px;
  stroke: currentColor;
  stroke-width: 8px;
  width: 30px;
}

.menu-button__icon path:nth-child(1),
.menu-button__icon path:nth-child(3) {
  transition: transform 0.3s ease-in-out;
}
.menu-button.active .menu-button__icon path:nth-child(1) {
  transform: translate(-15%, 38%) rotate(-45deg) scaleX(0.6);
}
.menu-button.active .menu-button__icon path:nth-child(3) {
  transform: translate(55%, -8%) rotate(45deg) scaleX(0.6);
}
</style>
