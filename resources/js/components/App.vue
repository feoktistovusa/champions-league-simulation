<template>
  <div class="min-h-screen bg-gray-100">
    <div class="container mx-auto px-4 py-8">
      <h1 class="text-4xl font-bold text-center mb-8 text-gray-800">
        Champions League Simulation
      </h1>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- League Table -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h2 class="text-2xl font-semibold mb-4">League Table</h2>
          <div v-if="!Array.isArray(standings) || standings.length === 0" class="text-gray-500 text-center py-4">
            Loading standings...
          </div>
          <LeagueTable v-else :standings="standings" />
        </div>

        <!-- Match Results -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-semibold">
              Week {{ currentWeek }} Results
            </h2>
            <div class="space-x-2">
              <button
                @click="previousWeek"
                :disabled="currentWeek <= 1"
                class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:bg-gray-300"
              >
                &lt;
              </button>
              <button
                @click="nextWeek"
                :disabled="currentWeek >= totalWeeks"
                class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:bg-gray-300"
              >
                &gt;
              </button>
            </div>
          </div>
          <div v-if="!Array.isArray(currentWeekMatches) || currentWeekMatches.length === 0" class="text-gray-500 text-center py-4">
            No matches for this week
          </div>
          <MatchResults 
            v-else
            :matches="currentWeekMatches" 
            @update-match="handleUpdateMatch"
          />
        </div>
      </div>

      <!-- Predictions (shown after week 4) -->
      <div v-if="currentWeek > 4 && predictions.length > 0" class="mt-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h2 class="text-2xl font-semibold mb-4">Championship Predictions</h2>
          <Predictions :predictions="predictions" />
        </div>
      </div>

      <!-- Control Buttons -->
      <div class="mt-8 flex justify-center space-x-4">
        <button
          @click="simulateWeek"
          :disabled="allMatchesPlayed"
          class="px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 disabled:bg-gray-300 font-semibold"
        >
          Play Next Week
        </button>
        <button
          @click="simulateAll"
          :disabled="allMatchesPlayed"
          class="px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:bg-gray-300 font-semibold"
        >
          Play All Matches
        </button>
        <button
          @click="resetLeague"
          class="px-6 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 font-semibold"
        >
          Reset League
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import LeagueTable from './LeagueTable.vue';
import MatchResults from './MatchResults.vue';
import Predictions from './Predictions.vue';

export default {
  components: {
    LeagueTable,
    MatchResults,
    Predictions
  },
  setup() {
    const standings = ref([]);
    const matches = ref([]);
    const predictions = ref([]);
    const currentWeek = ref(1);
    const totalWeeks = ref(6);
    const allMatchesPlayed = ref(false);
    const displayWeek = ref(1);

    const currentWeekMatches = computed(() => {
      if (!Array.isArray(matches.value)) return [];
      return matches.value.filter(match => match?.week === displayWeek.value);
    });

    const fetchStandings = async () => {
      try {
        const response = await axios.get('/api/standings');
        standings.value = response.data.data || [];
      } catch (error) {
        console.error('Error fetching standings:', error);
        standings.value = [];
      }
    };

    const fetchMatches = async () => {
      try {
        const response = await axios.get('/api/matches');
        matches.value = response.data.data || [];
      } catch (error) {
        console.error('Error fetching matches:', error);
        matches.value = [];
      }
    };

    const fetchCurrentWeek = async () => {
      try {
        const response = await axios.get('/api/current-week');
        const data = response.data.data || response.data;
        currentWeek.value = data.current_week || 1;
        totalWeeks.value = data.total_weeks || 6;
        allMatchesPlayed.value = data.all_matches_played || false;
        displayWeek.value = Math.min(currentWeek.value, totalWeeks.value);
      } catch (error) {
        console.error('Error fetching current week:', error);
        currentWeek.value = 1;
        totalWeeks.value = 6;
        allMatchesPlayed.value = false;
        displayWeek.value = 1;
      }
    };

    const fetchPredictions = async () => {
      try {
        const response = await axios.get('/api/predictions');
        predictions.value = response.data.data || [];
      } catch (error) {
        console.error('Error fetching predictions:', error);
        predictions.value = [];
      }
    };

    const simulateWeek = async () => {
      try {
        await axios.post('/api/simulate-week');
        await fetchAll();
      } catch (error) {
        console.error('Error simulating week:', error);
      }
    };

    const simulateAll = async () => {
      try {
        await axios.post('/api/simulate-all');
        await fetchAll();
      } catch (error) {
        console.error('Error simulating all matches:', error);
      }
    };

    const resetLeague = async () => {
      if (!confirm('Are you sure you want to reset the league?')) return;
      
      try {
        await axios.post('/api/reset-league');
        await fetchAll();
      } catch (error) {
        console.error('Error resetting league:', error);
      }
    };

    const handleUpdateMatch = async (matchId, scores) => {
      try {
        await axios.put(`/api/matches/${matchId}`, scores);
        await fetchAll();
      } catch (error) {
        console.error('Error updating match:', error);
      }
    };

    const previousWeek = () => {
      if (displayWeek.value > 1) {
        displayWeek.value--;
      }
    };

    const nextWeek = () => {
      if (displayWeek.value < totalWeeks.value) {
        displayWeek.value++;
      }
    };

    const fetchAll = async () => {
      await Promise.all([
        fetchStandings(),
        fetchMatches(),
        fetchCurrentWeek(),
        fetchPredictions()
      ]);
    };

    onMounted(() => {
      fetchAll();
    });

    watch(currentWeek, () => {
      fetchPredictions();
    });

    return {
      standings,
      matches,
      predictions,
      currentWeek,
      totalWeeks,
      allMatchesPlayed,
      displayWeek,
      currentWeekMatches,
      simulateWeek,
      simulateAll,
      resetLeague,
      handleUpdateMatch,
      previousWeek,
      nextWeek
    };
  }
};
</script>