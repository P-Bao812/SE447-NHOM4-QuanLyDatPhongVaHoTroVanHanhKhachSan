<template>
  <div class="cong-viec-ve-sinh">
    <!-- FILTER BAR -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div class="d-flex flex-wrap align-items-center gap-2">
        <!-- Task Type Filter -->
        <div class="dropdown">
          <button class="filter-dropdown-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-tag text-muted"></i>
            <span>{{ filterCategory === 'all' ? 'Mọi công việc' : filterCategory }}</span>
          </button>
          <ul class="dropdown-menu shadow-sm">
            <li><a class="dropdown-item" href="#" @click.prevent="filterCategory = 'all'">Mọi công việc</a></li>
            <li><a class="dropdown-item" href="#" @click.prevent="filterCategory = 'Dọn phòng'">Dọn phòng</a></li>
            <li><a class="dropdown-item" href="#" @click.prevent="filterCategory = 'Bảo trì'">Bảo trì</a></li>
            <li><a class="dropdown-item" href="#" @click.prevent="filterCategory = 'Kiểm tra phòng'">Kiểm tra phòng</a></li>
            <li><a class="dropdown-item" href="#" @click.prevent="filterCategory = 'Hàng hóa'">Hàng hóa</a></li>
          </ul>
        </div>

        <!-- Assignee Filter -->
        <div class="dropdown">
          <button class="filter-dropdown-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-person text-muted"></i>
            <span>{{ filterAssignee === 'all' ? 'Mọi người phụ trách' : filterAssignee }}</span>
          </button>
          <ul class="dropdown-menu shadow-sm">
            <li><a class="dropdown-item" href="#" @click.prevent="filterAssignee = 'all'">Mọi người phụ trách</a></li>
            <li><a class="dropdown-item" href="#" @click.prevent="filterAssignee = 'Chị Hương'">Chị Hương</a></li>
            <li><a class="dropdown-item" href="#" @click.prevent="filterAssignee = 'Chị Hoa'">Chị Hoa</a></li>
            <li><a class="dropdown-item" href="#" @click.prevent="filterAssignee = 'Anh Tuấn'">Anh Tuấn</a></li>
            <li><a class="dropdown-item" href="#" @click.prevent="filterAssignee = 'Mai Linh'">Mai Linh</a></li>
          </ul>
        </div>

        <!-- Priority Filter -->
        <div class="dropdown">
          <button class="filter-dropdown-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-flag text-muted"></i>
            <span>{{ filterPriority === 'all' ? 'Mọi ưu tiên' : ('Ưu tiên ' + filterPriority) }}</span>
          </button>
          <ul class="dropdown-menu shadow-sm">
            <li><a class="dropdown-item" href="#" @click.prevent="filterPriority = 'all'">Mọi ưu tiên</a></li>
            <li><a class="dropdown-item" href="#" @click.prevent="filterPriority = 'Cao'">Ưu tiên Cao</a></li>
            <li><a class="dropdown-item" href="#" @click.prevent="filterPriority = 'Vừa'">Ưu tiên Vừa</a></li>
            <li><a class="dropdown-item" href="#" @click.prevent="filterPriority = 'Thấp'">Ưu tiên Thấp</a></li>
          </ul>
        </div>
      </div>

      <div class="text-muted small">
        Hiển thị <strong>{{ filteredTasks.length }}</strong> công việc
      </div>
    </div>

    <!-- KANBAN BOARD (3 CỘT) -->
    <div class="row g-3">
      <!-- CỘT 1: CHƯA LÀM (pending) -->
      <div class="col-12 col-md-4">
        <div class="kanban-column">
          <div class="column-header">
            <div class="column-title">
              <span class="status-dot dot-pending"></span>
              <span>Chưa làm</span>
              <span class="count-badge">{{ pendingTasks.length }}</span>
            </div>
            <button
              class="btn btn-sm btn-link text-muted p-0 border-0"
              title="Thêm công việc chưa làm"
              @click="$emit('open-create', 'pending')"
            >
              <i class="bi bi-plus-circle fs-6"></i>
            </button>
          </div>

          <!-- Task Cards -->
          <div v-for="task in pendingTasks" :key="task.id" class="task-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <div class="d-flex align-items-center gap-2 fw-bold text-dark" style="font-size: 0.95rem;">
                <i :class="getLocationIcon(task.location)"></i>
                <span>{{ task.location }}</span>
              </div>
              <span :class="getPriorityBadgeClass(task.priority)">
                <span v-if="task.priority === 'Cao'">↑</span>
                {{ task.priority }}
              </span>
            </div>

            <div class="text-muted small mb-1" style="font-size: 0.76rem;">{{ task.category }}</div>
            <div class="fw-bold text-dark mb-1" style="font-size: 0.92rem;">{{ task.title }}</div>
            <p class="text-muted small mb-3" style="font-size: 0.8rem; line-height: 1.4;">{{ task.note }}</p>

            <!-- Footer: Assignee + Deadline -->
            <div class="d-flex align-items-center justify-content-between pt-2 border-top">
              <div class="d-flex align-items-center gap-2">
                <div class="avatar-circle avatar-sm">{{ task.assigneeInitial || getInitials(task.assignee) }}</div>
                <span class="small text-muted" style="font-size: 0.8rem;">{{ task.assignee }}</span>
              </div>
              <span class="small text-muted" style="font-size: 0.78rem;">{{ task.timeInfo }}</span>
            </div>

            <!-- Action buttons -->
            <div class="mt-2.5 pt-2 border-top d-flex justify-content-between align-items-center">
              <button
                class="btn btn-sm btn-link text-muted p-0 text-decoration-none"
                style="font-size: 0.74rem;"
                @click="$emit('open-status', task.location)"
              >
                <i class="bi bi-arrow-repeat me-1"></i>Trạng thái
              </button>
              <button
                class="btn btn-sm btn-outline-teal py-0 px-2.5"
                style="font-size: 0.76rem;"
                @click="$emit('move-task', task, 'doing')"
              >
                Bắt đầu làm <i class="bi bi-arrow-right ms-1"></i>
              </button>
            </div>
          </div>

          <div v-if="pendingTasks.length === 0" class="text-center py-5 text-muted small">
            <i class="bi bi-check2-circle fs-3 d-block mb-1 text-muted"></i>
            Không có công việc nào chưa làm
          </div>
        </div>
      </div>

      <!-- CỘT 2: ĐANG THỰC HIỆN (doing) -->
      <div class="col-12 col-md-4">
        <div class="kanban-column">
          <div class="column-header">
            <div class="column-title">
              <span class="status-dot dot-doing"></span>
              <span>Đang thực hiện</span>
              <span class="count-badge">{{ doingTasks.length }}</span>
            </div>
            <button
              class="btn btn-sm btn-link text-muted p-0 border-0"
              title="Thêm công việc đang làm"
              @click="$emit('open-create', 'doing')"
            >
              <i class="bi bi-plus-circle fs-6"></i>
            </button>
          </div>

          <!-- Task Cards -->
          <div v-for="task in doingTasks" :key="task.id" class="task-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <div class="d-flex align-items-center gap-2 fw-bold text-dark" style="font-size: 0.95rem;">
                <i :class="getLocationIcon(task.location)"></i>
                <span>{{ task.location }}</span>
              </div>
              <span :class="getPriorityBadgeClass(task.priority)">
                <span v-if="task.priority === 'Cao'">↑</span>
                {{ task.priority }}
              </span>
            </div>

            <div class="text-muted small mb-1" style="font-size: 0.76rem;">{{ task.category }}</div>
            <div class="fw-bold text-dark mb-1" style="font-size: 0.92rem;">{{ task.title }}</div>
            <p class="text-muted small mb-2" style="font-size: 0.8rem; line-height: 1.4;">{{ task.note }}</p>

            <!-- Ongoing Banner -->
            <div class="progress-banner-info" v-if="task.ongoingDetail">
              <i class="bi bi-lightning-charge-fill text-primary"></i>
              <span>{{ task.ongoingDetail }}</span>
            </div>

            <!-- Footer: Assignee + Expected Finish -->
            <div class="d-flex align-items-center justify-content-between pt-2 border-top">
              <div class="d-flex align-items-center gap-2">
                <div class="avatar-circle avatar-sm">{{ task.assigneeInitial || getInitials(task.assignee) }}</div>
                <span class="small text-muted" style="font-size: 0.8rem;">{{ task.assignee }}</span>
              </div>
              <span class="small text-muted" style="font-size: 0.78rem;">{{ task.timeInfo }}</span>
            </div>

            <!-- Action button: Báo cáo hoàn thành -->
            <div class="mt-2.5 pt-2 border-top d-flex justify-content-end gap-2">
              <button
                class="btn btn-sm btn-teal text-white py-1 px-3"
                style="font-size: 0.76rem;"
                @click="$emit('open-report', task)"
              >
                <i class="bi bi-clipboard-check me-1"></i> Báo cáo hoàn thành
              </button>
            </div>
          </div>

          <div v-if="doingTasks.length === 0" class="text-center py-5 text-muted small">
            <i class="bi bi-cup-hot fs-3 d-block mb-1 text-muted"></i>
            Không có công việc nào đang thực hiện
          </div>
        </div>
      </div>

      <!-- CỘT 3: HOÀN THÀNH (done) -->
      <div class="col-12 col-md-4">
        <div class="kanban-column">
          <div class="column-header">
            <div class="column-title">
              <span class="status-dot dot-done"></span>
              <span>Hoàn thành</span>
              <span class="count-badge">{{ doneTasks.length }}</span>
            </div>
            <span class="text-muted small" style="font-size: 0.74rem;">Đã bàn giao</span>
          </div>

          <!-- Task Cards -->
          <div v-for="task in doneTasks" :key="task.id" class="task-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <div class="d-flex align-items-center gap-2 fw-bold text-dark" style="font-size: 0.95rem;">
                <i :class="getLocationIcon(task.location)"></i>
                <span>{{ task.location }}</span>
              </div>
              <i class="bi bi-check-circle-fill text-success fs-5"></i>
            </div>

            <div class="text-muted small mb-1" style="font-size: 0.76rem;">{{ task.category }}</div>
            <div class="fw-bold text-dark mb-1" style="font-size: 0.92rem;">{{ task.title }}</div>
            <p class="text-muted small mb-3" style="font-size: 0.8rem; line-height: 1.4;">{{ task.note }}</p>

            <!-- Footer: Assignee + Finished Time -->
            <div class="d-flex align-items-center justify-content-between pt-2 border-top">
              <div class="d-flex align-items-center gap-2">
                <div class="avatar-circle avatar-sm">{{ task.assigneeInitial || getInitials(task.assignee) }}</div>
                <span class="small text-muted" style="font-size: 0.8rem;">{{ task.assignee }}</span>
              </div>
              <span class="small text-muted" style="font-size: 0.78rem;">{{ task.timeInfo }}</span>
            </div>

            <!-- Button to Inspect / Verify room -->
            <div class="mt-2.5 pt-2 border-top text-end" v-if="task.category === 'Dọn phòng'">
              <button
                class="btn btn-sm btn-outline-success py-0 px-2"
                style="font-size: 0.72rem;"
                @click="$emit('open-inspection', task.location)"
              >
                <i class="bi bi-shield-check me-1"></i> Nghiệm thu phòng
              </button>
            </div>
          </div>

          <div v-if="doneTasks.length === 0" class="text-center py-5 text-muted small">
            <i class="bi bi-hourglass-split fs-3 d-block mb-1 text-muted"></i>
            Chưa có công việc nào hoàn thành
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CongViecVeSinh',
  props: {
    tasks: {
      type: Array,
      default: () => []
    }
  },
  emits: ['move-task', 'open-report', 'open-inspection', 'open-status', 'open-create'],
  data() {
    return {
      filterCategory: 'all',
      filterAssignee: 'all',
      filterPriority: 'all'
    };
  },
  computed: {
    filteredTasks() {
      return this.tasks.filter(t => {
        const matchCategory = this.filterCategory === 'all' || t.category === this.filterCategory;
        const matchAssignee = this.filterAssignee === 'all' || t.assignee === this.filterAssignee;
        const matchPriority = this.filterPriority === 'all' || t.priority === this.filterPriority;
        return matchCategory && matchAssignee && matchPriority;
      });
    },
    pendingTasks() {
      return this.filteredTasks.filter(t => t.status === 'pending');
    },
    doingTasks() {
      return this.filteredTasks.filter(t => t.status === 'doing');
    },
    doneTasks() {
      return this.filteredTasks.filter(t => t.status === 'done');
    }
  },
  methods: {
    getLocationIcon(location) {
      const loc = (location || '').toLowerCase();
      if (loc.includes('kho')) return 'bi bi-boxes';
      if (loc.includes('405') || loc.includes('bảo trì')) return 'bi bi-wrench';
      return 'bi bi-door-closed';
    },
    getPriorityBadgeClass(priority) {
      if (priority === 'Cao') return 'priority-badge-high';
      if (priority === 'Vừa') return 'priority-badge-medium';
      return 'priority-badge-low';
    },
    getInitials(name) {
      if (!name) return 'NV';
      if (name.includes('Hương')) return 'HƯ';
      if (name.includes('Hoa')) return 'HO';
      if (name.includes('Tuấn')) return 'TU';
      if (name.includes('Linh')) return 'ML';
      return name.substring(0, 2).toUpperCase();
    }
  }
};
</script>

<style scoped src="./buongphong.css"></style>
