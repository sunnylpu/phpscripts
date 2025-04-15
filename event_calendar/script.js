document.addEventListener('DOMContentLoaded', () => {
    let events = JSON.parse(localStorage.getItem('events')) || [];
    let calendar = null;

    // Initialize FullCalendar
    function initCalendar() {
        const calendarEl = document.getElementById('calendar');
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: events.map(event => ({
                id: event.id,
                title: event.title,
                start: event.start,
                end: event.end,
                color: event.color,
                description: event.description
            })),
            eventClick: function(info) {
                showEventDetails(info.event);
            },
            dateClick: function(info) {
                openEventModal(info.date);
            }
        });
        calendar.render();
    }

    // Event Form Handling
    const eventForm = document.getElementById('eventForm');
    const clearFormBtn = document.getElementById('clearForm');

    eventForm.addEventListener('submit', (e) => {
        e.preventDefault();
        saveEvent();
    });

    clearFormBtn.addEventListener('click', () => {
        eventForm.reset();
    });

    function saveEvent() {
        const eventData = {
            id: Date.now(),
            title: document.getElementById('eventTitle').value,
            start: document.getElementById('eventStart').value,
            end: document.getElementById('eventEnd').value,
            color: document.getElementById('eventColor').value,
            description: document.getElementById('eventDescription').value
        };

        events.push(eventData);
        localStorage.setItem('events', JSON.stringify(events));
        
        calendar.addEvent(eventData);
        eventForm.reset();
        updateEventsList();
    }

    // Events List
    function updateEventsList() {
        const eventsList = document.getElementById('eventsList');
        eventsList.innerHTML = '';

        events.forEach(event => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${event.title}</td>
                <td>${formatDateTime(event.start)}</td>
                <td>${formatDateTime(event.end)}</td>
                <td>${event.description || '-'}</td>
                <td>
                    <button onclick="editEvent(${event.id})" class="edit-btn">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteEvent(${event.id})" class="delete-btn">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            eventsList.appendChild(row);
        });
    }

    function formatDateTime(dateTimeString) {
        const date = new Date(dateTimeString);
        return date.toLocaleString();
    }

    // Event Modal
    const eventModal = document.getElementById('eventModal');
    const closeModalBtn = document.getElementById('closeModal');

    function showEventDetails(event) {
        const modalContent = document.getElementById('modalContent');
        modalContent.innerHTML = `
            <div class="event-details">
                <h4>${event.title}</h4>
                <p><strong>Start:</strong> ${formatDateTime(event.start)}</p>
                <p><strong>End:</strong> ${formatDateTime(event.end)}</p>
                <p><strong>Description:</strong> ${event.extendedProps.description || '-'}</p>
            </div>
            <div class="modal-actions">
                <button onclick="editEvent(${event.id})" class="primary-btn">Edit</button>
                <button onclick="deleteEvent(${event.id})" class="secondary-btn">Delete</button>
            </div>
        `;
        eventModal.style.display = 'block';
    }

    function openEventModal(date) {
        const modalContent = document.getElementById('modalContent');
        modalContent.innerHTML = `
            <form id="quickEventForm" class="space-y-4">
                <div class="form-group">
                    <label for="quickEventTitle">Event Title</label>
                    <input type="text" id="quickEventTitle" required>
                </div>
                <div class="form-group">
                    <label for="quickEventStart">Start Time</label>
                    <input type="time" id="quickEventStart" required>
                </div>
                <div class="form-group">
                    <label for="quickEventEnd">End Time</label>
                    <input type="time" id="quickEventEnd" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="primary-btn">Add Event</button>
                </div>
            </form>
        `;
        eventModal.style.display = 'block';

        const quickEventForm = document.getElementById('quickEventForm');
        quickEventForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const startTime = document.getElementById('quickEventStart').value;
            const endTime = document.getElementById('quickEventEnd').value;
            
            const eventData = {
                id: Date.now(),
                title: document.getElementById('quickEventTitle').value,
                start: `${date.toISOString().split('T')[0]}T${startTime}`,
                end: `${date.toISOString().split('T')[0]}T${endTime}`,
                color: '#4f46e5',
                description: ''
            };

            events.push(eventData);
            localStorage.setItem('events', JSON.stringify(events));
            calendar.addEvent(eventData);
            updateEventsList();
            closeModal();
        });
    }

    function closeModal() {
        eventModal.style.display = 'none';
    }

    closeModalBtn.addEventListener('click', closeModal);

    window.addEventListener('click', (e) => {
        if (e.target === eventModal) {
            closeModal();
        }
    });

    // Event Actions
    window.editEvent = function(id) {
        const event = events.find(e => e.id === id);
        if (event) {
            document.getElementById('eventTitle').value = event.title;
            document.getElementById('eventStart').value = event.start;
            document.getElementById('eventEnd').value = event.end;
            document.getElementById('eventColor').value = event.color;
            document.getElementById('eventDescription').value = event.description;
            
            // Remove the event from the calendar and events array
            const calendarEvent = calendar.getEventById(id);
            if (calendarEvent) {
                calendarEvent.remove();
            }
            events = events.filter(e => e.id !== id);
            localStorage.setItem('events', JSON.stringify(events));
            updateEventsList();
        }
    };

    window.deleteEvent = function(id) {
        if (confirm('Are you sure you want to delete this event?')) {
            const calendarEvent = calendar.getEventById(id);
            if (calendarEvent) {
                calendarEvent.remove();
            }
            events = events.filter(e => e.id !== id);
            localStorage.setItem('events', JSON.stringify(events));
            updateEventsList();
            closeModal();
        }
    };

    // Initialize
    initCalendar();
    updateEventsList();
}); 