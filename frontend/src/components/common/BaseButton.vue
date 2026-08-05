<script setup lang="ts">
withDefaults(
  defineProps<{
    variant?: 'primary' | 'secondary'
    disabled?: boolean
  }>(),
  { variant: 'primary', disabled: false },
)
</script>

<template>
  <button class="base-button" :class="variant" :disabled="disabled">
    <slot />
  </button>
</template>

<style scoped>
.base-button {
  padding: 0.6em 1.4em;
  border-radius: 6px;
  border: 1px solid transparent;
  font-size: 1rem;
  cursor: pointer;
  transition: filter 0.15s ease;
}

.base-button:disabled {
  cursor: not-allowed;
  opacity: 0.5;
}

.base-button.primary {
  background-color: #a8483c;
  color: #f2f0e6;
}

.base-button.secondary {
  background-color: transparent;
  border-color: #6b6b7a;
  color: #f2f0e6;
}

.base-button:not(:disabled):hover {
  filter: brightness(1.1);
}
</style>
