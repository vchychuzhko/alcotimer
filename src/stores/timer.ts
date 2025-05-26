import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

import type { TimerMode } from '@/api/TimerMode.ts'
import type { TimerState } from '@/api/TimerState'
import { getFromLocalStorage, setToLocalStorage } from '@/service/storage'

const STATE_STORAGE_KEY = 'timer_state'

export const useTimerStore = defineStore('timer', () => {
  const state = ref<TimerState>({
    active: false,
    remaining: null,
    mode_id: 1,
    show_time: true,
  })

  const loadState = () => {
    const stateStored = getFromLocalStorage(STATE_STORAGE_KEY)

    if (stateStored) {
      state.value = stateStored as TimerState
    }
  }
  const saveState = () => {
    setToLocalStorage(STATE_STORAGE_KEY, state.value as object)
  }

  const setActive = (active: boolean) => {
    state.value.active = active

    saveState()
  }
  const setRemaining = (remaining: number | null) => {
    state.value.remaining = remaining

    saveState()
  }
  const setModeId = (modeId: number) => {
    state.value.mode_id = modeId

    saveState()
  }
  const setShowTime = (showTime: boolean) => {
    state.value.show_time = showTime

    saveState()
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

  return { state, mode, loadState, setActive, setRemaining, setModeId, setShowTime, getAllModes }
})
