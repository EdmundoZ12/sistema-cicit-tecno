<template>
  <div class="relative">
    <div @click="open = !open">
      <slot name="trigger" />
    </div>

    <!-- Full Screen Overlay -->
    <div v-show="open" class="fixed inset-0 z-40" @click="open = false"></div>

    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-show="open"
        class="absolute z-50 mt-2 rounded-md shadow-lg"
        :class="[widthClass, alignmentClasses]"
        style="display: none"
        :style="{ display: open ? 'block' : 'none' }"
      >
        <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white dark:bg-gray-700">
          <slot name="content" />
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'

interface Props {
  align?: 'left' | 'right'
  width?: '48' | '96'
  contentClasses?: string
}

const props = withDefaults(defineProps<Props>(), {
  align: 'right',
  width: '48',
  contentClasses: 'py-1 bg-white dark:bg-gray-700'
})

const open = ref(false)

const closeOnEscape = (e: KeyboardEvent) => {
  if (open.value && e.key === 'Escape') {
    open.value = false
  }
}

onMounted(() => document.addEventListener('keydown', closeOnEscape))
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape))

const widthClass = computed(() => {
  return {
    '48': 'w-48',
    '96': 'w-96'
  }[props.width.toString()]
})

const alignmentClasses = computed(() => {
  if (props.align === 'left') {
    return 'ltr:origin-top-left rtl:origin-top-right start-0'
  } else if (props.align === 'right') {
    return 'ltr:origin-top-right rtl:origin-top-left end-0'
  }

  return 'origin-top'
})
</script>
