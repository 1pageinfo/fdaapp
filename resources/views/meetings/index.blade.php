@extends('layouts.app')

@section('styles')
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css" rel="stylesheet">
  <style>
    #calendar { min-height: 640px; }

    /* Toolbar buttons (Prev/Today/Next) — force visibility regardless of theme */
    .fc .fc-button,
    .fc-theme-standard .fc-button,
    .fc .fc-button-primary {
      background: #145c32 !important;   /* app green */
      border: none !important;
      color: #fff !important;
      padding: .36rem .7rem !important;
      border-radius: .375rem !important;
      font-size: .875rem !important;
      line-height: 1.25rem !important;
      text-transform: none !important;
      min-width: 64px !important;
    }
    .fc .fc-button:hover { background: #0f4726 !important; }
    .fc .fc-button:disabled { opacity: .6 !important; }
    .fc .fc-toolbar-title { font-size: 1.25rem; font-weight: 600; }

    /* Ensure event text uses inline textColor and not grey pills */
    .fc .fc-event,
    .fc .fc-event .fc-event-main,
    .fc .fc-daygrid-event .fc-event-time,
    .fc .fc-daygrid-event .fc-event-title {
      color: inherit !important;
      background: transparent !important;
      padding: 0 !important;
      border-radius: 0 !important;
    }
    /* Nice pill when we render block events */
    .fc .fc-daygrid-event.fc-event {
      padding: 2px 6px !important;
      border-radius: .375rem !important;
    }

    /* Our custom header view buttons */
    .seg-btn-group .btn {
      border-radius: .375rem;
    }
    .seg-btn-group .btn + .btn {
      margin-left: .25rem;
    }
  </style>
@endsection

@section('content')
  <div class="content-wrapper">
    <div class="row">
      <div class="col-12">

        {{-- Header --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
          <h2 class="mb-2 mb-sm-0 d-flex align-items-center">
            Meetings & Events
          </h2>

          <div class="d-flex flex-wrap align-items-center">
            {{-- Our own Month/Week/Day view switch (not FullCalendar toolbar) --}}
            <div class="seg-btn-group mr-2">
              <button id="btnMonth" class="btn btn-outline-secondary btn-sm">Month</button>
              <button id="btnWeek"  class="btn btn-outline-secondary btn-sm">Week</button>
              <button id="btnDay"   class="btn btn-outline-secondary btn-sm">Day</button>
            </div>

            {{-- Page view: Calendar/List --}}
            <div class="btn-group mr-2" role="group" aria-label="Page view switch">
              <button id="btnViewCalendar" class="btn btn-outline-primary btn-sm active" type="button">
                <i class="ti-layout-grid3 mr-1"></i> Calendar
              </button>
              <button id="btnViewList" class="btn btn-outline-secondary btn-sm" type="button">
                <i class="ti-view-list mr-1"></i> List
              </button>
            </div>

            <a href="{{ route('meetings.create') }}" class="btn btn-primary btn-sm">
              <span class="ti-plus mr-1"></span> Add Meeting/Event
            </a>
          </div>
        </div>

        <hr>
        {{-- Calendar --}}
        <div id="calendarWrap" class="card mb-3">
          <div class="card-body">
            <div id="calendar"></div>
          </div>
        </div>

        {{-- List view --}}
        <div id="listWrap" class="card d-none">
          <div class="card-body p-0">
            <div id="eventList" class="list-group list-group-flush"></div>
          </div>
        </div>

      </div>
    </div>
  </div>

  {{-- Details Modal --}}
  <div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 id="eventModalLabel" class="modal-title">Event details</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="mb-2"><div class="text-muted small">When</div><div id="evWhen" class="font-weight-medium"></div></div>
          <div class="mb-2"><div class="text-muted small">Type</div><div id="evType"></div></div>
          <div class="mb-2 d-none" id="evGroupWrap"><div class="text-muted small">Group</div><div id="evGroup"></div></div>
          <div class="mb-2 d-none" id="evLocationWrap"><div class="text-muted small">Location</div><div id="evLocation"></div></div>
          <div class="mb-2 d-none" id="evDescWrap"><div class="text-muted small">Description</div><div id="evDesc"></div></div>
          <div class="mt-3"><a id="evOpenLink" href="#" class="btn btn-primary btn-sm">Open details</a></div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
  <script>
    (function () {
      document.addEventListener('DOMContentLoaded', function () {
        const CURRENT_USER_ID = @json(auth()->id());

        // Modal helper
        function showModalSafely(sel){
          if (window.jQuery && typeof jQuery.fn.modal === 'function') { jQuery(sel).modal('show'); return; }
          const el = document.querySelector(sel); if (!el) return;
          el.classList.add('show'); el.style.display='block'; el.removeAttribute('aria-hidden'); el.setAttribute('aria-modal','true');
          document.body.classList.add('modal-open');
          const back = document.createElement('div'); back.className='modal-backdrop fade show'; back.dataset.fallback='1'; document.body.appendChild(back);
          el.querySelectorAll('[data-dismiss="modal"], .close').forEach(btn => btn.addEventListener('click', () => {
            el.classList.remove('show'); el.style.display='none'; document.body.classList.remove('modal-open');
            const b = document.querySelector('.modal-backdrop[data-fallback="1"]'); if (b) b.remove();
          }));
        }

        const RAW_EVENTS = @json($events);

        // Normalize events + colors + readable text
        const events = (Array.isArray(RAW_EVENTS) ? RAW_EVENTS : []).map(e => {
          const type = (e.type || e.category || '').toString().toLowerCase();
          let color = '#3a87ad';
          if (type === 'event') color = '#28a745';
          if (e.status === 'going') color = '#ffc107';

          const ownerIds = [e.owner_id, e.user_id, e.created_by_id, e.created_by];
          const isMine = !!(e.mine || e.is_owner) || ownerIds.some(id => id != null && String(id) === String(CURRENT_USER_ID));

          const needsDarkText = (hex) => {
            const m = hex.replace('#','').match(/.{1,2}/g);
            if (!m) return false;
            const [r,g,b] = m.map(h => parseInt(h,16));
            const L = (0.299*r + 0.587*g + 0.114*b) / 255;
            return L > 0.6;
          };
          const textColor = needsDarkText(color) ? '#000' : '#fff';

          return {
            id: e.id ?? e.uuid ?? String(Math.random()),
            title: e.title ?? e.name ?? 'Untitled',
            start: e.start ?? e.start_at ?? e.starts_at ?? e.date ?? null,
            end: e.end ?? e.end_at ?? e.ends_at ?? null,
            allDay: !!(e.allDay || e.all_day),
            backgroundColor: color,
            borderColor: color,
            textColor: textColor,
            extendedProps: {
              type, rawType: e.type ?? e.category ?? null,
              group: e.group ?? e.group_name ?? null,
              location: e.location ?? e.venue ?? null,
              description: e.description ?? e.notes ?? '',
              url: e.url ?? e.show_url ?? e.link ?? ('{{ route('meetings.index') }}' + '/' + (e.id ?? '')),
              mine: isMine,
              status: e.status ?? null
            }
          };
        });

        // Elements
        const calendarEl = document.getElementById('calendar');
        const calendarWrap = document.getElementById('calendarWrap');
        const listWrap = document.getElementById('listWrap');
        const eventList = document.getElementById('eventList');
        const btnViewCalendar = document.getElementById('btnViewCalendar');
        const btnViewList = document.getElementById('btnViewList');
        const btnMonth = document.getElementById('btnMonth');
        const btnWeek  = document.getElementById('btnWeek');
        const btnDay   = document.getElementById('btnDay');

        // Modal fields
        const modalTitle = document.getElementById('eventModalLabel');
        const evWhen = document.getElementById('evWhen');
        const evType = document.getElementById('evType');
        const evGroup = document.getElementById('evGroup');
        const evGroupWrap = document.getElementById('evGroupWrap');
        const evLocation = document.getElementById('evLocation');
        const evLocationWrap = document.getElementById('evLocationWrap');
        const evDesc = document.getElementById('evDesc');
        const evDescWrap = document.getElementById('evDescWrap');
        const evOpenLink = document.getElementById('evOpenLink');

        // Calendar (no right-side toolbar; we control view via our own buttons)
        let calendar = null;
        function renderCalendar(viewName){
          if (!calendarEl) return;
          if (calendar) calendar.destroy();
          calendar = new FullCalendar.Calendar(calendarEl, {
            themeSystem: 'standard',
            buttonIcons: false,
            customButtons: {
              myPrev: { text: 'Prev',  click: () => calendar.prev() },
              myNext: { text: 'Next',  click: () => calendar.next() }
            },
            headerToolbar: {
              left: 'myPrev today myNext',
              center: 'title',
              right: ''                   // ✅ nothing here — avoids theme conflicts
            },
            buttonText: { today: 'Today' },
            initialView: viewName || 'dayGridMonth',
            height: 'auto',
            events,
            eventDisplay: 'block',        // month shows colored blocks
            eventClick: (info) => { info.jsEvent.preventDefault(); openModalFromFC(info.event); }
          });
          calendar.render();
        }

        // List view
        function fmtWhen(ev){
          try {
            const s = new Date(ev.start);
            const e = ev.end ? new Date(ev.end) : null;
            const dOpt = { year:'numeric', month:'short', day:'numeric' };
            const tOpt = { hour:'2-digit', minute:'2-digit' };
            if (ev.allDay) {
              if (e && s.toDateString() !== e.toDateString())
                return `${s.toLocaleDateString(undefined,dOpt)} (All day) → ${e.toLocaleDateString(undefined,dOpt)} (All day)`;
              return `${s.toLocaleDateString(undefined,dOpt)} · All day`;
            }
            if (e) {
              const same = s.toDateString() === e.toDateString();
              return same
                ? `${s.toLocaleDateString(undefined,dOpt)} · ${s.toLocaleTimeString(undefined,tOpt)}–${e.toLocaleTimeString(undefined,tOpt)}`
                : `${s.toLocaleDateString(undefined,dOpt)} ${s.toLocaleTimeString(undefined,tOpt)} → ${e.toLocaleDateString(undefined,dOpt)} ${e.toLocaleTimeString(undefined,tOpt)}`;
            }
            return `${s.toLocaleDateString(undefined,dOpt)} · ${s.toLocaleTimeString(undefined,tOpt)}`;
          } catch { return ev.start || ''; }
        }
        function esc(s){ return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
        function buildList(){
          if (!eventList) return;
          const sorted = events.slice().sort((a,b)=> new Date(a.start) - new Date(b.start));
          if (!sorted.length) { eventList.innerHTML = '<div class="p-4 text-center text-muted">No events.</div>'; return; }
          eventList.innerHTML = sorted.map(ev => {
            const ep = ev.extendedProps || {};
            const dot = ev.backgroundColor || '#3a87ad';
            const group = ep.group ? `<span class="mx-2 text-muted">•</span><span>${esc(ep.group)}</span>` : '';
            const loc = ep.location ? `<div class="text-muted small mt-1"><i class="ti-location-pin mr-1"></i>${esc(ep.location)}</div>` : '';
            const type = ep.type ? `<span class="badge badge-light border">${ep.type.charAt(0).toUpperCase()+ep.type.slice(1)}</span>` : '';
            return `
              <a class="list-group-item list-group-item-action event-item p-3" href="#" data-eid="${esc(ev.id)}">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <div class="d-flex align-items-center">
                      <span class="mr-2" style="display:inline-block;width:.5rem;height:.5rem;border-radius:999px;background:${dot}"></span>
                      <span class="font-weight-bold">${esc(ev.title)}</span>
                    </div>
                    <div class="text-muted small mt-1"><i class="ti-time mr-1"></i>${esc(fmtWhen(ev))} ${group}</div>
                    ${loc}
                  </div>
                  <div>${type}</div>
                </div>
              </a>`;
          }).join('');
          eventList.querySelectorAll('[data-eid]').forEach(a => {
            a.addEventListener('click', (e) => {
              e.preventDefault();
              const id = a.getAttribute('data-eid');
              const ev = events.find(x => String(x.id) === String(id));
              if (ev) openModalFromData(ev);
            });
          });
        }

        // Modal
        function openModalFromFC(fcEvent){
          openModalFromData({
            title: fcEvent.title,
            start: fcEvent.start,
            end: fcEvent.end,
            allDay: fcEvent.allDay,
            extendedProps: fcEvent.extendedProps || {}
          });
        }
        function openModalFromData(ev){
          const ep = ev.extendedProps || {};
          modalTitle.textContent = ev.title || 'Event details';
          evWhen.textContent = fmtWhen(ev);
          evType.textContent = ep.type ? (ep.type.charAt(0).toUpperCase()+ep.type.slice(1)) : (ep.rawType || '—');
          if (ep.group){ evGroupWrap.classList.remove('d-none'); evGroup.textContent = ep.group; } else { evGroupWrap.classList.add('d-none'); }
          if (ep.location){ evLocationWrap.classList.remove('d-none'); evLocation.textContent = ep.location; } else { evLocationWrap.classList.add('d-none'); }
          if (ep.description){ evDescWrap.classList.remove('d-none'); evDesc.textContent = ep.description; } else { evDescWrap.classList.add('d-none'); }
          evOpenLink.setAttribute('href', ep.url || '#');
          showModalSafely('#eventModal');
        }

        // Page view switching
        const showCalendar = (viewName) => {
          calendarWrap.classList.remove('d-none');
          listWrap.classList.add('d-none');
          btnViewCalendar.classList.add('active');
          btnViewList.classList.remove('active');
          renderCalendar(viewName);
          highlightViewButton(viewName || 'dayGridMonth');
        };
        const showList = () => {
          calendarWrap.classList.add('d-none');
          listWrap.classList.remove('d-none');
          btnViewCalendar.classList.remove('active');
          btnViewList.classList.add('active');
          buildList();
          highlightViewButton(null);
        };

        // Our Month/Week/Day buttons control FullCalendar view
        function highlightViewButton(view){
          [btnMonth, btnWeek, btnDay].forEach(b => b && b.classList.remove('btn-success', 'text-white'));
          if (view === 'dayGridMonth' && btnMonth) { btnMonth.classList.add('btn-success','text-white'); }
          if (view === 'timeGridWeek'  && btnWeek)  { btnWeek.classList.add('btn-success','text-white'); }
          if (view === 'timeGridDay'   && btnDay)   { btnDay.classList.add('btn-success','text-white'); }
        }
        btnMonth?.addEventListener('click', () => { if (calendar) { calendar.changeView('dayGridMonth'); highlightViewButton('dayGridMonth'); }});
        btnWeek ?.addEventListener('click', () => { if (calendar) { calendar.changeView('timeGridWeek');  highlightViewButton('timeGridWeek');  }});
        btnDay  ?.addEventListener('click', () => { if (calendar) { calendar.changeView('timeGridDay');   highlightViewButton('timeGridDay');   }});

        btnViewCalendar?.addEventListener('click', () => showCalendar(calendar?.view?.type));
        btnViewList?.addEventListener('click', showList);

        // Initial render (Month)
        showCalendar('dayGridMonth');
      });
    })();
  </script>
@endsection
