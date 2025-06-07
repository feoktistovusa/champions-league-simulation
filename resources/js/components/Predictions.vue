<template>
  <div class="space-y-4">
    <div
      v-for="prediction in predictions"
      :key="prediction.team.id"
      class="flex items-center justify-between"
    >
      <div class="flex items-center space-x-3">
        <span class="text-lg font-medium">{{ prediction.team.name }}</span>
      </div>
      <div class="flex items-center space-x-3">
        <div class="w-48 bg-gray-200 rounded-full h-6 relative">
          <div
            class="bg-blue-600 h-6 rounded-full flex items-center justify-end pr-2 text-white text-sm font-semibold"
            :style="{ width: prediction.probability + '%' }"
          >
            {{ prediction.probability }}%
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    predictions: {
      type: Array,
      required: true
    }
  }
};
</script>