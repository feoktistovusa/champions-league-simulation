<template>
  <div class="space-y-4">
    <div
      v-for="match in matches"
      :key="match.id"
      class="border rounded-lg p-4 hover:shadow-md transition-shadow"
      :class="{
        'bg-gray-50': !match.played,
        'bg-white': match.played
      }"
    >
      <div class="flex items-center justify-between">
        <div class="flex-1 text-right font-medium">
          {{ match.home_team.name }}
        </div>
        <div class="px-4 text-center">
          <template v-if="match.played && !editingMatch[match.id]">
            <span class="text-2xl font-bold">
              {{ match.home_score }} - {{ match.away_score }}
            </span>
          </template>
          <template v-else-if="editingMatch[match.id]">
            <div class="flex items-center space-x-2">
              <input
                v-model.number="editScores[match.id].home_score"
                type="number"
                min="0"
                max="10"
                class="w-12 text-center border rounded px-1 py-1"
              />
              <span>-</span>
              <input
                v-model.number="editScores[match.id].away_score"
                type="number"
                min="0"
                max="10"
                class="w-12 text-center border rounded px-1 py-1"
              />
            </div>
          </template>
          <template v-else>
            <span class="text-gray-400">vs</span>
          </template>
        </div>
        <div class="flex-1 text-left font-medium">
          {{ match.away_team.name }}
        </div>
      </div>
      
      <div v-if="match.played" class="mt-2 text-center">
        <button
          v-if="!editingMatch[match.id]"
          @click="startEdit(match)"
          class="text-sm text-blue-600 hover:text-blue-800"
        >
          Edit Result
        </button>
        <div v-else class="space-x-2">
          <button
            @click="saveEdit(match.id)"
            class="text-sm text-green-600 hover:text-green-800"
          >
            Save
          </button>
          <button
            @click="cancelEdit(match.id)"
            class="text-sm text-red-600 hover:text-red-800"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive } from 'vue';

export default {
  props: {
    matches: {
      type: Array,
      required: true
    }
  },
  emits: ['update-match'],
  setup(props, { emit }) {
    const editingMatch = ref({});
    const editScores = reactive({});

    const startEdit = (match) => {
      editingMatch.value[match.id] = true;
      editScores[match.id] = {
        home_score: match.home_score,
        away_score: match.away_score
      };
    };

    const saveEdit = (matchId) => {
      emit('update-match', matchId, editScores[matchId]);
      editingMatch.value[matchId] = false;
    };

    const cancelEdit = (matchId) => {
      editingMatch.value[matchId] = false;
      delete editScores[matchId];
    };

    return {
      editingMatch,
      editScores,
      startEdit,
      saveEdit,
      cancelEdit
    };
  }
};
</script>