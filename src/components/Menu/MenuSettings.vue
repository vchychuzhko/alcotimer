<script setup lang="ts">
import { useTimerStore } from '@/stores/timer.ts'
import Checkbox from 'primevue/checkbox'
import SelectButton from 'primevue/selectbutton'
import { ref } from 'vue'

const timerStore = useTimerStore()

const modeId = ref<number>(timerStore.state.mode_id)
const showTime = ref<boolean>(timerStore.state.show_time)

const setModeId = () => {
  timerStore.setModeId(modeId.value)
}
const setShowTime = () => {
  timerStore.setShowTime(showTime.value)
}
</script>

<template>
  <div class="settings">
    <SelectButton
      class="settings__mode"
      v-model="modeId"
      :options="timerStore.getAllModes()"
      option-label="label"
      option-value="id"
      @change="setModeId"
    />
    <label class="settings__time">
      <Checkbox v-model="showTime" binary @change="setShowTime" />
      <span>Show time</span>
    </label>
  </div>
</template>

<style scoped>
.settings {
  align-items: flex-start;
  display: flex;
  flex-direction: column;
  row-gap: 1rem;
}
.settings__mode {
  align-self: stretch;
}
.settings__mode:deep(.p-togglebutton) {
  flex: 1;
}
.settings__time {
  align-items: center;
  column-gap: 0.5rem;
  cursor: pointer;
  display: flex;
}
</style>
