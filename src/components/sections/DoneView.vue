<template>
  <div class='done-view'>
    <h3 class='done-view headline'>Done ({{ tasks.filter(task => new RegExp(searchQuery.name).test(task.title)).length }})</h3>

    <ul class='done-view tasks'>
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

const tasks = taskService.getDoneTasks()

const onTaskUpdated = () => taskService.fetchUpdatedTasks()
</script>