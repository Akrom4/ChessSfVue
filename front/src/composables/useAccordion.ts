// src/composables/useAccordion.ts
import { ref } from 'vue'

export function useAccordion() {
  const openDisclosureIndex = ref<number | null>(null)
  function toggleDisclosure(index: number) {
    openDisclosureIndex.value = openDisclosureIndex.value === index ? null : index
  }
  return { openDisclosureIndex, toggleDisclosure }
}
