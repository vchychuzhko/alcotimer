import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

import type { TimerMode } from '@/api/TimerMode'
import type { TimerState } from '@/api/TimerState'

export const useTimerStore = defineStore(
  'timer',
  () => {
    const state = ref<TimerState>({
      active: false,
      remaining: null,
      mode_id: 1,
      show_time: true,
    })

    const setActive = (active: boolean) => {
      state.value.active = active
    }
    const setRemaining = (remaining: number | null) => {
      state.value.remaining = remaining
    }
    const setModeId = (modeId: number) => {
      state.value.mode_id = modeId
    }
    const setShowTime = (showTime: boolean) => {
      state.value.show_time = showTime
    }

    const getAllModes = (): TimerMode[] => {
      return [
        { id: 1, label: 'Light', from: 120, to: 300 },
        { id: 2, label: 'Medium', from: 60, to: 120 },
        { id: 3, label: 'Heavy', from: 30, to: 60 },
      ]
    }

    const mode = computed<TimerMode>(() => {
      const modes = getAllModes()

      return modes.find(({ id }) => id === state.value.mode_id) || modes[0]
    })

    return { state, mode, setActive, setRemaining, setModeId, setShowTime, getAllModes }
  },
  {
    persist: true,
  },
)
