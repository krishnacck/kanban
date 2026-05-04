import './bootstrap';
import Alpine from 'alpinejs';
import Sortable from 'sortablejs';

window.Alpine = Alpine;

function boardApp() {
    return {
        filters: {
            search: '',
            priority: '',
            country_id: '',
            status_id: '',
            assigned_to: '',
        },
        groupRow: 'category',   // category | priority | assignee | month | none
        groupCol: 'status',     // status | priority | month
        modal: {
            open: false,
            taskId: null,
            saving: false,
            deleting: false,
            errorMessage: '',
            errors: {},
            form: {
                title: '',
                description: '',
                priority: 'medium',
                status_id: '',
                country_id: '',
                assigned_to: '',
                due_date: '',
            },
        },
        ctxMenu:      { open: false, x: 0, y: 0, type: null, data: {} },
        renamePopover:{ open: false, x: 0, y: 0, label: '', value: '', type: null, id: null },
        sortables: [],
        _draggingOver: false,

        init() {
            this.initSortable();
        },

        initSortable() {
            // Destroy old instances
            this.sortables.forEach(s => s.destroy());
            this.sortables = [];

            const trashZone  = document.getElementById('trash-zone');
            const trashInner = document.getElementById('trash-inner');
            const trashIcon  = document.getElementById('trash-icon');

            // ── Trash helpers ──────────────────────────────────────────────
            const showTrash = () => {
                if (!trashZone) return;
                trashZone.style.opacity  = '1';
                trashZone.style.transform = 'scale(1)';
                trashZone.style.pointerEvents = 'auto';
            };
            const hideTrash = () => {
                if (!trashZone) return;
                trashZone.style.opacity  = '0';
                trashZone.style.transform = 'scale(0.9)';
                trashZone.style.pointerEvents = 'none';
                this._draggingOver = false;
                setTrashHighlight(false);
            };
            const setTrashHighlight = (on) => {
                if (!trashInner || !trashIcon) return;
                if (on) {
                    trashInner.style.background   = '#fef2f2';
                    trashInner.style.borderColor  = '#ef4444';
                    trashInner.style.transform    = 'scale(1.15)';
                    trashIcon.style.color         = '#dc2626';
                } else {
                    trashInner.style.background   = '#ffffff';
                    trashInner.style.borderColor  = '#fca5a5';
                    trashInner.style.transform    = 'scale(1)';
                    trashIcon.style.color         = '#f87171';
                }
            };

            // ── Pointer tracking for trash detection ───────────────────────
            let lastX = 0, lastY = 0;
            const onPointerMove = (e) => {
                lastX = e.clientX;
                lastY = e.clientY;
                if (!trashZone) return;
                const r   = trashZone.getBoundingClientRect();
                const hit = e.clientX >= r.left && e.clientX <= r.right &&
                            e.clientY >= r.top  && e.clientY <= r.bottom;
                if (hit !== this._draggingOver) {
                    this._draggingOver = hit;
                    setTrashHighlight(hit);
                }
            };

            // ── Create one Sortable per cell ───────────────────────────────
            document.querySelectorAll('[data-cell]').forEach(cell => {
                const origStatusId  = cell.dataset.statusId;
                const origCountryId = cell.dataset.countryId;

                const s = Sortable.create(cell, {
                    group:       'board',          // shared group = cross-cell drag
                    animation:   150,
                    ghostClass:  'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass:   'sortable-drag',
                    // No draggable/handle — any child can be dragged
                    // No filter — clicks handled by Alpine @click

                    onStart: () => {
                        showTrash();
                        document.addEventListener('pointermove', onPointerMove);
                    },

                    onEnd: async (evt) => {
                        document.removeEventListener('pointermove', onPointerMove);

                        const taskId = evt.item.dataset.taskId;
                        if (!taskId) { hideTrash(); return; }

                        // Final trash check using last pointer coords
                        const overTrash = (() => {
                            if (!trashZone) return false;
                            const r = trashZone.getBoundingClientRect();
                            return lastX >= r.left && lastX <= r.right &&
                                   lastY >= r.top  && lastY <= r.bottom;
                        })();

                        hideTrash();

                        if (overTrash || this._draggingOver) {
                            this._draggingOver = false;
                            // Put card back so board refresh is clean
                            const origCell = document.querySelector(
                                `[data-cell][data-status-id="${origStatusId}"][data-country-id="${origCountryId}"]`
                            );
                            if (origCell) origCell.prepend(evt.item);
                            await this._deleteTaskById(taskId);
                            return;
                        }

                        const targetCell      = evt.to;
                        const targetStatusId  = targetCell.dataset.statusId;
                        const targetCountryId = targetCell.dataset.countryId;

                        if (!targetStatusId || !targetCountryId) return;

                        // Count only task-card siblings for position
                        const siblings = Array.from(targetCell.children)
                            .filter(el => el.dataset.taskId);
                        const position = siblings.indexOf(evt.item);

                        const res = await fetch(`/tasks/${taskId}/move`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': window.__CSRF_TOKEN__,
                                'Accept':       'application/json',
                            },
                            body: JSON.stringify({
                                status_id:  targetStatusId,
                                country_id: targetCountryId,
                                position:   Math.max(0, position),
                            }),
                        });

                        if (!res.ok) {
                            // Revert DOM on failure
                            const origCell = document.querySelector(
                                `[data-cell][data-status-id="${origStatusId}"][data-country-id="${origCountryId}"]`
                            );
                            if (origCell) {
                                const ref = Array.from(origCell.children)
                                    .filter(el => el.dataset.taskId)[evt.oldIndex] || null;
                                origCell.insertBefore(evt.item, ref);
                            }
                            const err = await res.json().catch(() => ({}));
                            console.error('Move failed:', err.message || res.status);
                        }
                    },
                });

                this.sortables.push(s);
            });
        },

        async changePriority(taskId, direction) {
            // direction: 'up' = increase (low→medium→high), 'down' = decrease
            const order = ['low', 'medium', 'high'];
            // We don't know current priority here, so fetch it from the card's data attribute
            const card = document.querySelector(`[data-task-id="${taskId}"]`);
            if (!card) return;
            const current = card.dataset.priority;
            const idx = order.indexOf(current);
            const next = direction === 'up'
                ? order[Math.min(idx + 1, order.length - 1)]
                : order[Math.max(idx - 1, 0)];
            if (next === current) return; // already at boundary

            const res = await fetch(`/tasks/${taskId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.__CSRF_TOKEN__, 'Accept': 'application/json' },
                body: JSON.stringify({ priority: next }),
            });
            if (res.ok) await this.applyFilters();
        },

        async completeTask(taskId) {            const res = await fetch(`/tasks/${taskId}/complete`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': window.__CSRF_TOKEN__, 'Accept': 'application/json' },
            });
            if (res.ok) await this.applyFilters();
        },

        async quickAddTask(statusId, countryId, title) {
            if (!title.trim()) return;
            const res = await fetch('/tasks', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.__CSRF_TOKEN__, 'Accept': 'application/json' },
                body: JSON.stringify({
                    title:      title.trim(),
                    status_id:  statusId,
                    country_id: countryId,
                    priority:   'medium',
                }),
            });
            if (res.ok) await this.applyFilters();
            else console.error('Quick add failed:', res.status);
        },

        async _deleteTaskById(taskId) {            const res = await fetch(`/tasks/${taskId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.__CSRF_TOKEN__,
                    'Accept':       'application/json',
                },
            });
            if (res.ok) {
                await this.applyFilters();
            } else {
                console.error('Delete failed:', res.status);
            }
        },

        async applyFilters() {
            const params = new URLSearchParams();
            Object.entries(this.filters).forEach(([k, v]) => { if (v) params.set(k, v); });
            // Pass grouping to server so it can return correct data
            params.set('group_row', this.groupRow);
            params.set('group_col', this.groupCol);

            const res = await fetch(`${window.__BOARD_URL__}?${params}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (res.ok) {
                document.getElementById('board-container').innerHTML = await res.text();
                this.$nextTick(() => this.initSortable());
            }
        },

        // Re-render with current grouping without re-fetching
        renderGrouped() {
            this.applyFilters();
        },

        filterByCountry(id) { this.filters.country_id = id; this.applyFilters(); },
        filterByCategory(id) { this.filterByCountry(id); },

        clearFilters() {
            this.filters = { search: '', priority: '', country_id: '', status_id: '', assigned_to: '' };
            this.applyFilters();
        },

        async moveCountry(id, direction) {
            const res = await fetch(`/countries/${id}/move`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.__CSRF_TOKEN__, 'Accept': 'application/json' },
                body: JSON.stringify({ direction }),
            });
            if (res.ok) await this.applyFilters();
        },

        async addCategory(name) {
            const res = await fetch('/categories', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.__CSRF_TOKEN__, 'Accept': 'application/json' },
                body: JSON.stringify({ name, order: (window.__COUNTRIES__?.length ?? 0) + 1 }),
            });
            if (res.ok) await this.applyFilters();
            else alert((await res.json().catch(() => ({}))).message || 'Could not add category.');
        },

        openCreateModal() {
            this._openModal(null, {
                title: '', description: '', priority: 'medium',
                status_id:  window.__STATUSES__?.[0]?.id  ?? '',
                country_id: window.__COUNTRIES__?.[0]?.id ?? '',
                assigned_to: '', due_date: '',
            });
        },

        openCreateModalForCell(statusId, countryId) {
            this._openModal(null, {
                title: '', description: '', priority: 'medium',
                status_id:  statusId  || (window.__STATUSES__?.[0]?.id  ?? ''),
                country_id: countryId || (window.__COUNTRIES__?.[0]?.id ?? ''),
                assigned_to: '', due_date: '',
            });
        },

        openEditModal(taskId, data) { this._openModal(taskId, data); },

        _openModal(taskId, form) {
            this.modal = { open: true, taskId, saving: false, deleting: false, errorMessage: '', errors: {}, form: { ...form } };
        },

        async saveTask() {
            this.modal.saving = true;
            this.modal.errors = {};
            this.modal.errorMessage = '';
            const isEdit = !!this.modal.taskId;
            try {
                const res = await fetch(isEdit ? `/tasks/${this.modal.taskId}` : '/tasks', {
                    method: isEdit ? 'PUT' : 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.__CSRF_TOKEN__, 'Accept': 'application/json' },
                    body: JSON.stringify(this.modal.form),
                });
                if (res.ok) { this.modal.open = false; await this.applyFilters(); }
                else {
                    const d = await res.json();
                    if (d.errors) this.modal.errors = d.errors;
                    else this.modal.errorMessage = d.message || 'An error occurred.';
                }
            } catch { this.modal.errorMessage = 'Network error.'; }
            finally   { this.modal.saving = false; }
        },

        async deleteTask() {
            if (!this.modal.taskId || !confirm('Delete this task?')) return;
            this.modal.deleting = true;
            try {
                const res = await fetch(`/tasks/${this.modal.taskId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': window.__CSRF_TOKEN__, 'Accept': 'application/json' },
                });
                if (res.ok) { this.modal.open = false; await this.applyFilters(); }
                else this.modal.errorMessage = (await res.json()).message || 'Could not delete.';
            } catch { this.modal.errorMessage = 'Network error.'; }
            finally   { this.modal.deleting = false; }
        },

        // ── Context menu ───────────────────────────────────────────────────
        openContextMenu(event, type, data) {
            this.ctxMenu.open = false;
            this.renamePopover.open = false;
            const x = Math.min(event.clientX, window.innerWidth  - 220);
            const y = Math.min(event.clientY, window.innerHeight - 220);
            this.ctxMenu = { open: true, x, y, type, data };
            const close = () => { this.ctxMenu.open = false; document.removeEventListener('click', close); };
            setTimeout(() => document.addEventListener('click', close), 0);
        },

        ctxEditTask() {
            this.ctxMenu.open = false;
            const d = this.ctxMenu.data;
            this._openModal(parseInt(d.id), {
                title: d.title || '', description: d.description || '', priority: d.priority || 'medium',
                status_id: d.status_id, country_id: d.country_id,
                assigned_to: d.assigned_to || '', due_date: d.due_date || '',
            });
        },
        ctxRenameTask()    { this.ctxMenu.open = false; this._openRenamePopover('task',    this.ctxMenu.data.id, 'Rename task',     this.ctxMenu.data.title); },
        ctxDeleteTask()    { this.ctxMenu.open = false; if (confirm('Delete this task?')) this._deleteTaskById(this.ctxMenu.data.id); },
        ctxRenameCountry() { this.ctxMenu.open = false; this._openRenamePopover('country', this.ctxMenu.data.id, 'Rename category', this.ctxMenu.data.name); },
        ctxRenameStatus()  { this.ctxMenu.open = false; this._openRenamePopover('status',  this.ctxMenu.data.id, 'Rename status',   this.ctxMenu.data.name); },

        _openRenamePopover(type, id, label, currentValue) {
            const x = Math.min(this.ctxMenu.x, window.innerWidth  - 270);
            const y = Math.min(this.ctxMenu.y, window.innerHeight - 130);
            this.renamePopover = { open: true, x, y, label, value: currentValue, type, id };
            this.$nextTick(() => { const el = document.querySelector('#rename-popover input'); if (el) { el.focus(); el.select(); } });
        },

        async submitRename() {
            const { type, id, value } = this.renamePopover;
            if (!value.trim()) return;
            this.renamePopover.open = false;
            const map = {
                task:    { url: `/tasks/${id}`,              method: 'PUT',   body: { title: value.trim() } },
                country: { url: `/countries/${id}/rename`,   method: 'PATCH', body: { name:  value.trim() } },
                status:  { url: `/statuses/${id}`,           method: 'PUT',   body: { name:  value.trim() } },
            };
            const cfg = map[type]; if (!cfg) return;
            const res = await fetch(cfg.url, {
                method: cfg.method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.__CSRF_TOKEN__, 'Accept': 'application/json' },
                body: JSON.stringify(cfg.body),
            });
            if (res.ok) await this.applyFilters();
            else alert((await res.json().catch(() => ({}))).message || 'Could not rename.');
        },
    };
}

window.boardApp = boardApp;
Alpine.start();
