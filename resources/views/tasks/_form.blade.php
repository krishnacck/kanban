<div x-show="modal.open" x-cloak
    style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:50;padding:1rem;backdrop-filter:blur(4px);"
    @keydown.escape.window="modal.open = false">
    <div class="md-dialog" style="width:100%;max-width:520px;" @click.outside="modal.open = false">

        {{-- Header --}}
        <div style="padding:1.5rem 1.5rem 0;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                <h2 style="font-size:1.375rem;font-weight:600;color:#1C1B1F;" x-text="modal.taskId ? 'Edit Task' : 'New Task'"></h2>
                <button @click="modal.open = false"
                    style="width:40px;height:40px;border-radius:50%;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#49454F;transition:background 0.15s;"
                    onmouseover="this.style.background='#F4EFF4'" onmouseout="this.style.background='transparent'">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div style="padding:0 1.5rem 1rem;display:flex;flex-direction:column;gap:1rem;">

            {{-- Title --}}
            <div>
                <label style="display:block;font-size:0.75rem;font-weight:600;color:#49454F;letter-spacing:0.05em;text-transform:uppercase;margin-bottom:0.375rem;">Title *</label>
                <input type="text" x-model="modal.form.title" placeholder="What needs to be done?"
                    style="width:100%;background:#F4EFF4;border:none;border-bottom:2px solid #79747E;border-radius:4px 4px 0 0;padding:0.75rem 1rem;font-size:0.9375rem;color:#1C1B1F;outline:none;transition:border-color 0.2s;"
                    onfocus="this.style.borderBottomColor='#6750A4'" onblur="this.style.borderBottomColor='#79747E'">
                <p x-show="modal.errors.title" x-text="modal.errors.title" style="color:#B3261E;font-size:0.75rem;margin-top:0.25rem;"></p>
            </div>

            {{-- Description --}}
            <div>
                <label style="display:block;font-size:0.75rem;font-weight:600;color:#49454F;letter-spacing:0.05em;text-transform:uppercase;margin-bottom:0.375rem;">Description</label>
                <textarea x-model="modal.form.description" rows="3" placeholder="Add more details…"
                    style="width:100%;background:#F4EFF4;border:none;border-bottom:2px solid #79747E;border-radius:4px 4px 0 0;padding:0.75rem 1rem;font-size:0.875rem;color:#1C1B1F;outline:none;resize:none;transition:border-color 0.2s;font-family:inherit;"
                    onfocus="this.style.borderBottomColor='#6750A4'" onblur="this.style.borderBottomColor='#79747E'"></textarea>
            </div>

            {{-- Status + Category --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                <div>
                    <label style="display:block;font-size:0.75rem;font-weight:600;color:#49454F;letter-spacing:0.05em;text-transform:uppercase;margin-bottom:0.375rem;">Status *</label>
                    <select x-model="modal.form.status_id"
                        style="width:100%;background:#F4EFF4;border:none;border-bottom:2px solid #79747E;border-radius:4px 4px 0 0;padding:0.75rem 1rem;font-size:0.875rem;color:#1C1B1F;outline:none;cursor:pointer;"
                        onfocus="this.style.borderBottomColor='#6750A4'" onblur="this.style.borderBottomColor='#79747E'">
                        <template x-for="s in window.__STATUSES__" :key="s.id">
                            <option :value="s.id" x-text="s.name" :selected="modal.form.status_id == s.id"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:0.75rem;font-weight:600;color:#49454F;letter-spacing:0.05em;text-transform:uppercase;margin-bottom:0.375rem;">Category *</label>
                    <select x-model="modal.form.country_id"
                        style="width:100%;background:#F4EFF4;border:none;border-bottom:2px solid #79747E;border-radius:4px 4px 0 0;padding:0.75rem 1rem;font-size:0.875rem;color:#1C1B1F;outline:none;cursor:pointer;"
                        onfocus="this.style.borderBottomColor='#6750A4'" onblur="this.style.borderBottomColor='#79747E'">
                        <template x-for="c in window.__COUNTRIES__" :key="c.id">
                            <option :value="c.id" x-text="c.name" :selected="modal.form.country_id == c.id"></option>
                        </template>
                    </select>
                </div>
            </div>

            {{-- Priority + Start Date + Due Date --}}
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0.75rem;">
                <div>
                    <label style="display:block;font-size:0.75rem;font-weight:600;color:#49454F;letter-spacing:0.05em;text-transform:uppercase;margin-bottom:0.375rem;">Priority</label>
                    <select x-model="modal.form.priority"
                        style="width:100%;background:#F4EFF4;border:none;border-bottom:2px solid #79747E;border-radius:4px 4px 0 0;padding:0.75rem 1rem;font-size:0.875rem;color:#1C1B1F;outline:none;cursor:pointer;"
                        onfocus="this.style.borderBottomColor='#6750A4'" onblur="this.style.borderBottomColor='#79747E'">
                        <option value="high">🔴 High</option>
                        <option value="medium">🟡 Medium</option>
                        <option value="low">🟢 Low</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:0.75rem;font-weight:600;color:#49454F;letter-spacing:0.05em;text-transform:uppercase;margin-bottom:0.375rem;">Start Date</label>
                    <input type="date" x-model="modal.form.start_date"
                        style="width:100%;background:#F4EFF4;border:none;border-bottom:2px solid #79747E;border-radius:4px 4px 0 0;padding:0.75rem 1rem;font-size:0.875rem;color:#1C1B1F;outline:none;"
                        onfocus="this.style.borderBottomColor='#6750A4'" onblur="this.style.borderBottomColor='#79747E'">
                </div>
                <div>
                    <label style="display:block;font-size:0.75rem;font-weight:600;color:#49454F;letter-spacing:0.05em;text-transform:uppercase;margin-bottom:0.375rem;">Due Date</label>
                    <input type="date" x-model="modal.form.due_date"
                        style="width:100%;background:#F4EFF4;border:none;border-bottom:2px solid #79747E;border-radius:4px 4px 0 0;padding:0.75rem 1rem;font-size:0.875rem;color:#1C1B1F;outline:none;"
                        onfocus="this.style.borderBottomColor='#6750A4'" onblur="this.style.borderBottomColor='#79747E'">
                </div>
            </div>

            {{-- Assignee --}}
            <div>
                <label style="display:block;font-size:0.75rem;font-weight:600;color:#49454F;letter-spacing:0.05em;text-transform:uppercase;margin-bottom:0.375rem;">Assignee</label>
                <select x-model="modal.form.assigned_to"
                    style="width:100%;background:#F4EFF4;border:none;border-bottom:2px solid #79747E;border-radius:4px 4px 0 0;padding:0.75rem 1rem;font-size:0.875rem;color:#1C1B1F;outline:none;cursor:pointer;"
                    onfocus="this.style.borderBottomColor='#6750A4'" onblur="this.style.borderBottomColor='#79747E'">
                    <option value="">Unassigned</option>
                    <template x-for="u in window.__USERS__" :key="u.id">
                        <option :value="u.id" x-text="u.name" :selected="modal.form.assigned_to == u.id"></option>
                    </template>
                </select>
            </div>

            <p x-show="modal.errorMessage" x-text="modal.errorMessage"
                style="color:#B3261E;font-size:0.875rem;background:#F9DEDC;border-radius:8px;padding:0.625rem 0.875rem;"></p>
        </div>

        {{-- Footer --}}
        <div style="padding:0.75rem 1.5rem 1.5rem;display:flex;align-items:center;justify-content:space-between;border-top:1px solid #E7E0EC;">
            <div>
                <template x-if="modal.taskId">
                    <button @click="deleteTask()" class="md-btn-text" style="color:#B3261E;" onmouseover="this.style.background='#F9DEDC'" onmouseout="this.style.background='transparent'">
                        Delete task
                    </button>
                </template>
            </div>
            <div style="display:flex;gap:0.5rem;">
                <button @click="modal.open = false" class="md-btn-outlined">Cancel</button>
                <button @click="saveTask()" :disabled="modal.saving" class="md-btn-filled md-ripple"
                    x-text="modal.saving ? 'Saving…' : (modal.taskId ? 'Update' : 'Create Task')"></button>
            </div>
        </div>
    </div>
</div>
