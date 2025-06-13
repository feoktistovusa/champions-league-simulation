<template>
  <div class="overflow-x-auto">
    <table class="min-w-full table-auto">
      <thead>
        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
          <th class="py-3 px-2 text-left">Pos</th>
          <th class="py-3 px-4 text-left">Team</th>
          <th class="py-3 px-2 text-center">P</th>
          <th class="py-3 px-2 text-center">W</th>
          <th class="py-3 px-2 text-center">D</th>
          <th class="py-3 px-2 text-center">L</th>
          <th class="py-3 px-2 text-center">GF</th>
          <th class="py-3 px-2 text-center">GA</th>
          <th class="py-3 px-2 text-center">GD</th>
          <th class="py-3 px-2 text-center font-bold">Pts</th>
        </tr>
      </thead>
      <tbody class="text-gray-600 text-sm">
        <tr
          v-for="(standing, index) in standings"
          :key="standing.id"
          class="border-b border-gray-200 hover:bg-gray-100"
          :class="{
            'bg-green-50': index === 0,
            'bg-blue-50': index === 1
          }"
        >
          <td class="py-3 px-2 text-left">{{ index + 1 }}</td>
          <td class="py-3 px-4 text-left font-medium">
            {{ standing.team?.name || 'Unknown Team' }}
          </td>
          <td class="py-3 px-2 text-center">{{ standing.played }}</td>
          <td class="py-3 px-2 text-center">{{ standing.won }}</td>
          <td class="py-3 px-2 text-center">{{ standing.drawn }}</td>
          <td class="py-3 px-2 text-center">{{ standing.lost }}</td>
          <td class="py-3 px-2 text-center">{{ standing.goals_for }}</td>
          <td class="py-3 px-2 text-center">{{ standing.goals_against }}</td>
          <td class="py-3 px-2 text-center">
            <span :class="{
              'text-green-600': standing.goal_difference > 0,
              'text-red-600': standing.goal_difference < 0
            }">
              {{ standing.goal_difference > 0 ? '+' : '' }}{{ standing.goal_difference }}
            </span>
          </td>
          <td class="py-3 px-2 text-center font-bold">{{ standing.points }}</td>
        </tr>
      </tbody>
    </table>
    <div class="mt-4 text-xs text-gray-500">
      <p><span class="inline-block w-4 h-4 bg-green-50 mr-2"></span>Champions League</p>
      <p><span class="inline-block w-4 h-4 bg-blue-50 mr-2"></span>Europa League</p>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    standings: {
      type: Array,
      required: true
    }
  }
};
</script>