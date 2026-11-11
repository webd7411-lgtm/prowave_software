/**
 * Prowave ERP — Global calendar (month / week / day / agenda)
 */
(function (window, document) {
    'use strict';

    window.Prowave = window.Prowave || {};

    function initCalendar() {
        const root = document.querySelector('[data-calendar]');
        if (!root) {
            return;
        }

        const state = {
            view: root.dataset.calendarView || 'month',
            current: new Date(),
            events: [],
        };

        const elViewBtns = document.querySelectorAll('[data-calendar-view-btn]');
        const elTitle = document.getElementById('calendar-title');
        const elGrid = document.getElementById('calendar-grid');
        const elSidebar = document.getElementById('calendar-upcoming');

        function pad(n) {
            return String(n).padStart(2, '0');
        }

        function fmtDate(d) {
            return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
        }

        function startOfRange() {
            const d = new Date(state.current);
            if (state.view === 'month') {
                d.setDate(1);
                d.setDate(1 - d.getDay());
            } else if (state.view === 'week') {
                d.setDate(d.getDate() - d.getDay());
            } else {
                d.setHours(0, 0, 0, 0);
            }
            return d;
        }

        function endOfRange() {
            const start = startOfRange();
            const d = new Date(start);
            if (state.view === 'month') {
                d.setDate(d.getDate() + 41);
            } else if (state.view === 'week') {
                d.setDate(d.getDate() + 6);
            } else {
                d.setHours(23, 59, 59, 999);
            }
            return d;
        }

        async function load() {
            if (!elGrid) return;
            elGrid.innerHTML = `
                <div class="p-5 text-center">
                    <div class="spinner-border text-primary" role="status" aria-label="Loading calendar"></div>
                </div>`;

            try {
                const start = fmtDate(startOfRange());
                const end = fmtDate(endOfRange());
                const res = await fetch(root.dataset.calendarUrl + `?start=${start}&end=${end}`, {
                    headers: { Accept: 'application/json' },
                });
                if (res.ok) {
                    state.events = await res.json();
                } else {
                    state.events = [];
                }
            } catch (e) {
                console.error('Calendar fetch error:', e);
                state.events = [];
            }
            render();
        }

        function eventsFor(dateStr, includeTime = false) {
            const dateKey = dateStr.slice(0, 10);
            return state.events.filter((e) => {
                const start = String(e.start || '').slice(0, 10);
                const end = String(e.end || '').slice(0, 10);
                if (includeTime && !e.allDay) {
                    return start === dateKey;
                }
                return start <= dateKey && (!end || end >= dateKey);
            });
        }

        function eventChip(e) {
            const link = document.createElement('a');
            link.className = 'cal-chip';
            link.style.background = e.color || '#2563EB';
            link.style.color = '#ffffff';
            link.textContent = `${e.type ? `[${e.type}] ` : ''}${e.title}`;
            link.title = `${e.title}${e.project ? ` — ${e.project}` : ''}`;
            link.href = e.url ? e.url : 'javascript:void(0)';
            return link;
        }

        function renderMonth() {
            const start = startOfRange();
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const header = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
                .map((d) => `<div class="cal-col-head">${d}</div>`)
                .join('');

            let cells = '';
            for (let i = 0; i < 42; i++) {
                const d = new Date(start);
                d.setDate(start.getDate() + i);
                const key = fmtDate(d);
                const inMonth = d.getMonth() === state.current.getMonth();
                const isToday = fmtDate(today) === key;
                const events = eventsFor(key).slice(0, 4);

                cells += `
                    <div class="cal-cell ${inMonth ? '' : 'cal-cell-muted'} ${isToday ? 'cal-cell-today' : ''}">
                        <div class="cal-cell-date">${d.getDate()}</div>
                        <div class="cal-cell-events">
                            ${events.map((e) => eventChip(e).outerHTML).join('')}
                            ${eventsFor(key).length > 4 ? `<div class="cal-more">+${eventsFor(key).length - 4} more</div>` : ''}
                        </div>
                    </div>`;
            }

            elGrid.innerHTML = `<div class="cal-grid cal-grid-month">${header}${cells}</div>`;
        }

        function renderWeek() {
            const start = startOfRange();
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const header = [];
            const bodies = [];
            for (let i = 0; i < 7; i++) {
                const d = new Date(start);
                d.setDate(start.getDate() + i);
                const key = fmtDate(d);
                const isToday = fmtDate(today) === key;
                const events = eventsFor(key);

                header.push(`
                    <div class="cal-col-head ${isToday ? 'cal-head-today' : ''}">
                        <div>${['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][d.getDay()]}</div>
                        <div class="cal-head-date">${d.getDate()}</div>
                    </div>`);
                bodies.push(`
                    <div class="cal-week-cell ${isToday ? 'cal-cell-today' : ''}">
                        ${events.map((e) => eventChip(e).outerHTML).join('') || '<span class="cal-empty-cell text-muted fs-xs">No events</span>'}
                    </div>`);
            }

            elGrid.innerHTML = `
                <div class="cal-grid cal-grid-week">${header.join('')}</div>
                <div class="cal-grid cal-grid-week">${bodies.join('')}</div>`;
        }

        function renderDay() {
            const key = fmtDate(state.current);
            const events = eventsFor(key, true).sort((a, b) => String(a.start).localeCompare(String(b.start)));

            elGrid.innerHTML = `
                <div class="cal-day-list p-3">
                    ${events.length ? events.map((e) => `
                        <div class="cal-day-item p-2 mb-2 rounded bg-body-tertiary" style="border-left: 4px solid ${e.color || '#2563EB'}">
                            <div class="cal-day-time fw-500 fs-xs text-muted mb-1">${e.allDay ? 'All day' : String(e.start || '').slice(11, 16) || '—'}</div>
                            <div class="flex-grow-1 min-w-0">
                                <a href="${e.url || 'javascript:void(0)'}" class="fw-600 text-body text-decoration-none">${e.title}</a>
                                <div class="fs-xs text-muted">${e.type || 'Event'}${e.project ? ` · ${e.project}` : ''}</div>
                            </div>
                        </div>`).join('') : `
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x fs-2 text-muted d-block mb-2"></i>
                            <span class="text-muted fs-sm">No events scheduled for this day</span>
                        </div>`}
                </div>`;
        }

        function renderAgenda() {
            const sorted = [...state.events].sort((a, b) => String(a.start).localeCompare(String(b.start)));

            elGrid.innerHTML = `
                <div class="cal-day-list p-3">
                    ${sorted.length ? sorted.map((e) => `
                        <div class="cal-day-item p-2 mb-2 rounded bg-body-tertiary d-flex align-items-center gap-3" style="border-left: 4px solid ${e.color || '#2563EB'}">
                            <div class="cal-day-time fw-600 fs-xs text-primary" style="min-width: 85px;">${String(e.start || '').slice(0, 10)}</div>
                            <div class="flex-grow-1 min-w-0">
                                <a href="${e.url || 'javascript:void(0)'}" class="fw-600 text-body text-decoration-none d-block text-truncate">${e.title}</a>
                                <div class="fs-xs text-muted">${e.type || 'Event'}${e.project ? ` · ${e.project}` : ''}</div>
                            </div>
                        </div>`).join('') : `
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x fs-2 text-muted d-block mb-2"></i>
                            <span class="text-muted fs-sm">No events in this range</span>
                        </div>`}
                </div>`;
        }

        function render() {
            const options = { month: 'long', year: 'numeric' };
            const weekYear = new Date(startOfRange());
            if (elTitle) {
                elTitle.textContent = state.view === 'day'
                    ? state.current.toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })
                    : state.view === 'week'
                        ? `Week of ${weekYear.toLocaleDateString(undefined, options)}`
                        : state.current.toLocaleDateString(undefined, options);
            }

            if (state.view === 'month') renderMonth();
            else if (state.view === 'week') renderWeek();
            else if (state.view === 'day') renderDay();
            else renderAgenda();

            elViewBtns.forEach((btn) => {
                const isSel = btn.dataset.calendarViewBtn === state.view;
                btn.classList.toggle('btn-primary', isSel);
                btn.classList.toggle('btn-outline-secondary', !isSel);
            });

            if (elSidebar) {
                const sorted = [...state.events].sort((a, b) => String(a.start).localeCompare(String(b.start)));
                elSidebar.innerHTML = sorted.slice(0, 10).map((e) => `
                    <a href="${e.url || 'javascript:void(0)'}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-2">
                        <span class="cal-dot rounded-circle flex-shrink-0" style="width:10px;height:10px;background:${e.color || '#2563EB'}"></span>
                        <span class="min-w-0 flex-grow-1">
                            <span class="d-block text-truncate fw-500 fs-sm text-body">${e.title}</span>
                            <span class="d-block fs-xs text-muted">${String(e.start || '').slice(0, 10)}${e.project ? ` · ${e.project}` : ''}</span>
                        </span>
                    </a>`).join('') || '<div class="p-3 text-muted fs-sm text-center">No upcoming events</div>';
            }
        }

        elViewBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                state.view = btn.dataset.calendarViewBtn;
                load();
            });
        });

        document.querySelectorAll('[data-calendar-nav]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const dir = parseInt(btn.dataset.calendarNav, 10);
                const d = new Date(state.current);
                if (state.view === 'month') d.setMonth(d.getMonth() + dir);
                else if (state.view === 'week') d.setDate(d.getDate() + dir * 7);
                else d.setDate(d.getDate() + dir);
                state.current = d;
                load();
            });
        });

        document.querySelectorAll('[data-calendar-today]').forEach((btn) => {
            btn.addEventListener('click', () => {
                state.current = new Date();
                load();
            });
        });

        load();
    }

    window.Prowave.initCalendar = initCalendar;
})(window, document);
