<script setup lang="ts">
import TimerButtonLoader from '@/components/Timer/Button/TimerButtonLoader.vue'
import Button from 'primevue/button'
import { ref, watch } from 'vue'

const props = defineProps<{
  active: boolean
}>()

const emit = defineEmits(['click'])

const playAnimation = ref<SVGAnimationElement>()
const pauseAnimation = ref<SVGAnimationElement>()

watch(
  () => props.active,
  (active) => {
    if (active) {
      pauseAnimation.value?.beginElement()
    } else {
      playAnimation.value?.beginElement()
    }
  },
  { immediate: true },
)
</script>

<template>
  <div class="timer-button-container">
    <TimerButtonLoader class="timer-button__loader" :class="{ show: active }" />
    <Button
      class="timer-button"
      icon="pi pi-sync"
      severity="secondary"
      rounded
      :title="active ? 'Pause' : 'Play'"
      @click="emit('click')"
    >
      <svg
        class="timer-button__icon"
        viewBox="0 0 40 40"
        stroke-width="4"
        stroke-linejoin="round"
        aria-hidden="true"
      >
        <path d="M6 6 L20 13 L20 27 L6 34 z M20 13 L34 20 L34 20 L20 27 z">
          <animate
            attributeName="d"
            to="M6 6 L14 6 L14 34 L6 34 z M26 6 L34 6 L34 34 L26 34 z"
            begin="-1"
            dur="0.3s"
            fill="freeze"
            ref="pauseAnimation"
          />
          <animate
            attributeName="d"
            to="M6 6 L20 13 L20 27 L6 34 z M20 13 L34 20 L34 20 L20 27 z"
            begin="-1"
            dur="0.3s"
            fill="freeze"
            ref="playAnimation"
          />
        </path>
      </svg>
    </Button>
  </div>
</template>

<style scoped>
.timer-button-container {
  background-color: var(--color-background);
  border: 2px solid var(--color-border);
  border-radius: 50%;
  padding: 10px;
  position: relative;
}

.timer-button__loader {
  inset: 12px;
  position: absolute;
  transition: inset 0.3s;
  z-index: 0;
}
.timer-button__loader.show {
  inset: 0;
}

.timer-button {
  position: relative;
  transition: none;
  z-index: 1;

  --p-button-icon-only-height: var(--button-size);
  --p-button-icon-only-width: var(--button-size);
}
.timer-button__icon {
  fill: var(--vt-c-white-soft);
  height: 50%;
  stroke: var(--vt-c-white-soft);
  width: 50%;
}

@media (prefers-color-scheme: light) {
  .timer-button {
    --p-button-secondary-background: #e60000;
    --p-button-secondary-hover-background: #cc0000;
    --p-button-secondary-active-background: #cc0000;
    --p-button-secondary-border-color: #e60000;
    --p-button-secondary-hover-border-color: #cc0000;
    --p-button-secondary-active-bord-colorer: #cc0000;
  }
}
</style>
