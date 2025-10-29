/**
 * ProjectFOB - Todos JavaScript
 */

class TodoManager {
    constructor() {
        if (typeof pfobData === 'undefined') return;

        this.restUrl = pfobData.restUrl;
        this.nonce = pfobData.nonce;
        this.projectId = pfobData.projectId;

        this.init();
    }

    init() {
        this.attachEventListeners();
        this.initDragAndDrop();
    }

    attachEventListeners() {
        // New list button
        const newListBtn = document.getElementById('new-todo-list-btn');
        if (newListBtn) {
            newListBtn.addEventListener('click', () => this.showCreateListModal());
        }

        // Add todo buttons
        document.querySelectorAll('.pfob-add-todo-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const listId = e.target.dataset.listId;
                this.showCreateTodoModal(listId);
            });
        });

        // Todo checkboxes
        document.querySelectorAll('.pfob-todo-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const todoItem = e.target.closest('.pfob-todo-item');
                const todoId = todoItem.dataset.todoId;
                this.toggleTodo(todoId, e.target.checked);
            });
        });

        // Edit buttons
        document.querySelectorAll('.pfob-edit-todo-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const todoItem = e.target.closest('.pfob-todo-item');
                const todoId = todoItem.dataset.todoId;
                this.showEditTodoModal(todoId);
            });
        });

        // Delete buttons
        document.querySelectorAll('.pfob-delete-todo-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const todoItem = e.target.closest('.pfob-todo-item');
                const todoId = todoItem.dataset.todoId;
                this.deleteTodo(todoId);
            });
        });
    }

    showCreateListModal() {
        const modal = document.createElement('div');
        modal.className = 'pfob-modal';
        modal.innerHTML = `
            <div class="pfob-modal-content">
                <div class="pfob-modal-header">
                    <h2>New To-do List</h2>
                    <button class="pfob-modal-close">&times;</button>
                </div>
                <div class="pfob-modal-body">
                    <form id="create-list-form">
                        <div class="pfob-form-group">
                            <label for="list-name">List Name *</label>
                            <input type="text" id="list-name" name="name" required
                                   placeholder="e.g. Launch Tasks">
                        </div>
                        <div class="pfob-form-group">
                            <label for="list-description">Description (optional)</label>
                            <textarea id="list-description" name="description" rows="3"
                                      placeholder="What's this list for?"></textarea>
                        </div>
                        <div class="pfob-form-actions">
                            <button type="submit" class="pfob-btn pfob-btn-primary">
                                Create List
                            </button>
                            <button type="button" class="pfob-btn pfob-btn-secondary pfob-modal-close">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        modal.querySelectorAll('.pfob-modal-close').forEach(btn => {
            btn.addEventListener('click', () => modal.remove());
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.remove();
        });

        document.getElementById('create-list-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.createList(new FormData(e.target));
        });

        document.getElementById('list-name').focus();
    }

    async createList(formData) {
        const data = {
            name: formData.get('name'),
            description: formData.get('description')
        };

        try {
            const response = await fetch(`${this.restUrl}/projects/${this.projectId}/todo-lists`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': this.nonce
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                document.querySelector('.pfob-modal')?.remove();
                window.location.reload();
            }
        } catch (error) {
            console.error('Failed to create list:', error);
            alert('Failed to create list. Please try again.');
        }
    }

    showCreateTodoModal(listId) {
        const modal = document.createElement('div');
        modal.className = 'pfob-modal';
        modal.innerHTML = `
            <div class="pfob-modal-content">
                <div class="pfob-modal-header">
                    <h2>New To-do</h2>
                    <button class="pfob-modal-close">&times;</button>
                </div>
                <div class="pfob-modal-body">
                    <form id="create-todo-form">
                        <div class="pfob-form-group">
                            <label for="todo-content">What needs to be done? *</label>
                            <input type="text" id="todo-content" name="content" required
                                   placeholder="e.g. Review design mockups">
                        </div>
                        <div class="pfob-form-group">
                            <label for="todo-description">Notes (optional)</label>
                            <textarea id="todo-description" name="description" rows="2"></textarea>
                        </div>
                        <div class="pfob-form-group">
                            <label for="todo-due-date">Due Date (optional)</label>
                            <input type="date" id="todo-due-date" name="due_date">
                        </div>
                        <div class="pfob-form-group">
                            <label for="todo-assignee">Assign to (optional)</label>
                            <select id="todo-assignee" name="assignee_id">
                                <option value="">Unassigned</option>
                                <!-- Will be populated via JavaScript -->
                            </select>
                        </div>
                        <div class="pfob-form-actions">
                            <button type="submit" class="pfob-btn pfob-btn-primary">
                                Add To-do
                            </button>
                            <button type="button" class="pfob-btn pfob-btn-secondary pfob-modal-close">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        modal.querySelectorAll('.pfob-modal-close').forEach(btn => {
            btn.addEventListener('click', () => modal.remove());
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.remove();
        });

        document.getElementById('create-todo-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.createTodo(listId, new FormData(e.target));
        });

        // Load project members
        this.loadProjectMembers();

        document.getElementById('todo-content').focus();
    }

    async loadProjectMembers() {
        try {
            const response = await fetch(`${this.restUrl}/projects/${this.projectId}/members`, {
                headers: { 'X-WP-Nonce': this.nonce }
            });

            const result = await response.json();

            if (result.success && result.data) {
                const select = document.getElementById('todo-assignee');
                result.data.forEach(member => {
                    const option = document.createElement('option');
                    option.value = member.ID;
                    option.textContent = member.display_name;
                    select.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Failed to load members:', error);
        }
    }

    async createTodo(listId, formData) {
        const data = {
            content: formData.get('content'),
            description: formData.get('description'),
            due_date: formData.get('due_date') || null,
            assignee_id: formData.get('assignee_id') || null
        };

        try {
            const response = await fetch(`${this.restUrl}/todo-lists/${listId}/items`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': this.nonce
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                document.querySelector('.pfob-modal')?.remove();
                window.location.reload();
            }
        } catch (error) {
            console.error('Failed to create todo:', error);
            alert('Failed to create to-do. Please try again.');
        }
    }

    async toggleTodo(todoId, isCompleted) {
        try {
            const endpoint = isCompleted
                ? `${this.restUrl}/todo-items/${todoId}/complete`
                : `${this.restUrl}/todo-items/${todoId}`;

            const response = await fetch(endpoint, {
                method: isCompleted ? 'POST' : 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': this.nonce
                },
                body: JSON.stringify({ is_completed: isCompleted ? 1 : 0 })
            });

            if (response.ok) {
                const todoItem = document.querySelector(`[data-todo-id="${todoId}"]`);
                todoItem.classList.toggle('pfob-completed', isCompleted);
            }
        } catch (error) {
            console.error('Failed to toggle todo:', error);
        }
    }

    async deleteTodo(todoId) {
        if (!confirm('Are you sure you want to delete this to-do?')) {
            return;
        }

        try {
            const response = await fetch(`${this.restUrl}/todo-items/${todoId}`, {
                method: 'DELETE',
                headers: { 'X-WP-Nonce': this.nonce }
            });

            if (response.ok) {
                const todoItem = document.querySelector(`[data-todo-id="${todoId}"]`);
                todoItem.remove();
            }
        } catch (error) {
            console.error('Failed to delete todo:', error);
            alert('Failed to delete to-do. Please try again.');
        }
    }

    initDragAndDrop() {
        const todoItems = document.querySelectorAll('.pfob-todo-item[draggable="true"]');

        todoItems.forEach(item => {
            item.addEventListener('dragstart', (e) => {
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', item.dataset.todoId);
                item.classList.add('pfob-dragging');
            });

            item.addEventListener('dragend', (e) => {
                item.classList.remove('pfob-dragging');
            });

            item.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
            });

            item.addEventListener('drop', async (e) => {
                e.preventDefault();
                const draggedId = e.dataTransfer.getData('text/plain');
                const droppedId = item.dataset.todoId;

                if (draggedId !== droppedId) {
                    // Reorder items
                    await this.reorderTodos(draggedId, droppedId);
                }
            });
        });
    }

    async reorderTodos(draggedId, targetId) {
        // Implementation for reordering todos
        console.log('Reorder:', draggedId, 'before', targetId);
        // Would need additional API endpoint to handle position updates
    }

    showEditTodoModal(todoId) {
        // Implementation for editing todos
        console.log('Edit todo:', todoId);
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    window.todoManager = new TodoManager();
});
