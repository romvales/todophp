<template>
  <div class='ongoing-view'>
    <h3 class='ongoing-view headline'>Ongoing ({{ tasks.filter(task => new RegExp(searchQuery.name).test(task.title)).length }})</h3>

    <ul class='ongoing-view tasks'>
      <template v-for='task of tasks' :key='task._id'>
        <li class='item' v-if='new RegExp(searchQuery.name).test(task.title)'>
          <TaskEditor :task='task' @updated='onTaskUpdated' />
        </li>
      </template>
    </ul>

  </div>
</template>

<script setup lang='ts'>
import { TaskService } from '@/lib/services/TaskService'
import TaskEditor from '@/components/partials/TaskEditor.vue'

const { taskService, searchQuery } = defineProps<{ taskService: TaskService, searchQuery: Record<string, any> }>()

const tasks = taskService.getOngoingTasks()
const onTaskUpdated = () => taskService.fetchUpdatedTasks()
</script>