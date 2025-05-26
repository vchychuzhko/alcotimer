<script setup lang="ts">
import audioSrc from '@/assets/audio/alert.mp3'
import TimerButton from '@/components/Timer/TimerButton.vue'
import { useTimerStore } from '@/stores/timer.ts'
import Button from 'primevue/button'
import { computed, onMounted, ref, watch } from 'vue'

const timerStore = useTimerStore()

let interval: number | undefined
const time = ref<number>(0)
const audio = ref<HTMLAudioElement>()

const checkIfStop = () => {
  if (time.value === 0) {
    stopTimer()

    timerStore.setRemaining(null)

    audio.value?.play()
  }
}
const startTimer = () => {
  timerStore.setActive(true)

  if (!timerStore.state.remaining) {
    timerStore.setRemaining(time.value)
  }

  interval = setInterval(() => {
    time.value = time.value - 1

    timerStore.setRemaining(time.value)

    checkIfStop()
  }, 1000)
}
const stopTimer = () => {
  timerStore.setActive(false)

  if (interval) {
    clearInterval(interval)
  }
}
const resetTimer = () => {
  const mode = timerStore.mode

  time.value = Math.floor(Math.random() * (mode.to - mode.from + 1)) + mode.from

  stopTimer()
  timerStore.setRemaining(null)
}

const toggleTimer = () => {
  if (timerStore.state.active) {
    stopTimer()
  } else {
    startTimer()
  }
}

const timeFormatted = computed(() => {
  const minutes = Math.trunc(time.value / 60)
  const seconds = Math.floor((time.value / 60 - minutes) * 60)

  return minutes + ':' + (seconds < 10 ? '0' : '') + seconds
})

onMounted(() => {
  timerStore.loadState()

  if (timerStore.state.remaining) {
    time.value = timerStore.state.remaining

    if (timerStore.state.active) {
      startTimer()
    }
  } else {
    resetTimer()
  }
})

watch(
  () => timerStore.state.mode_id,
  () => {
    resetTimer()
  },
)
</script>

<template>
  <section class="timer">
    <p class="timer__time" v-show="timerStore.state.show_time">{{ timeFormatted }}</p>
    <div class="timer__controls">
      <Button
        class="timer__reset"
        :class="{ show: !timerStore.state.active }"
        icon="pi pi-sync"
        severity="secondary"
        rounded
        size="large"
        title="Reset"
        @click="resetTimer"
      />
      <TimerButton :active="timerStore.state.active" @click="toggleTimer" />
    </div>
    <audio :src="audioSrc" ref="audio" hidden />
  </section>
</template>

<style scoped>
.timer {
  align-items: center;
  display: flex;
  flex-direction: column;
  justify-content: center;
  height: 100%;
  padding: 0.5rem 1rem;

  --button-size: 250px;
}

.timer__time {
  border: 1px solid var(--color-border);
  border-radius: 10px;
  font-size: 2.5rem;
  padding-inline: 1rem;
  transform: translateY(-100%);
}

.timer__controls {
  position: relative;
}
.timer__reset {
  position: absolute;
  right: 50%;
  top: 50%;
  transform: translate(50%, -50%);
  transition:
    right 0.3s,
    top 0.3s;
}
.timer__reset.show {
  right: 0;
  top: 0;
}
</style>
