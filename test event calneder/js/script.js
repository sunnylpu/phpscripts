// Initialize variables to store events
let events = JSON.parse(localStorage.getItem('calendarEvents')) || [];
let currentEventId = JSON.parse(localStorage.getItem('currentEventId')) || 1;
let selectedEvent = null;

// DOM Elements
const eventForm = document.getElementById('eventForm');
const eventsList = document.getElementById('eventsList');
const eventModal = document.getElementById('eventModal');
const closeModal = document.getElementById('closeModal');
const modalContent = document.getElementById('modalContent');
const clearFormBtn = document.getElementById('clearForm');

// Initialize FullCalendar
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the calendar
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        editable: true,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        events: events,
        // When a date is clicked, open the form with that date pre-filled
        dateClick: function(info) {
            document.getElementById('eventStart').value = formatDateForInput(info.dateStr);
            const endDate = new Date(info.dateStr);
            endDate.setHours(endDate.getHours() + 1);
            document.getElementById('eventEnd').value = formatDateForInput(formatDate(endDate));
        },
        // When an existing event is clicked, show event details in modal
        eventClick: function(info) {
            selectedEvent = events.find(event => event.id == info.event.id);
            showEventModal(selectedEvent);
        },
        // When an event is dragged and dropped
        eventDrop: function(info) {
            const eventId = parseInt(info.event.id);
            const eventIndex = events.findIndex(event => event.id === eventId);
            
            if (eventIndex !== -1) {
                events[eventIndex].start = info.event.start.toISOString();
                events[eventIndex].end = info.event.end ? info.event.end.toISOString() : null;
                saveEvents();
                renderEventsList();
            }
        },
        // When an event is resized
        eventResize: function(info) {
            const eventId = parseInt(info.event.id);
            const eventIndex = events.findIndex(event => event.id === eventId);
            
            if (eventIndex !== -1) {
                events[eventIndex].end = info.event.end.toISOString();
                saveEvents();
                renderEventsList();
            }
        }
    });
    
    calendar.render();
    
    // Global function to refresh calendar
    window.refreshCalendar = function() {
        calendar.removeAllEvents();
        calendar.addEventSource(events);
    };
    
    // Render the initial events list
    renderEventsList();
    
    // Event form submission
    eventForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const title = document.getElementById('eventTitle').value.trim();
        const start = document.getElementById('eventStart').value;
        const end = document.getElementById('eventEnd').value;
        const color = document.getElementById('eventColor').value;
        const description = document.getElementById('eventDescription').value.trim();
        
        if (!title || !start || !end) {
            alert('Please fill in all required fields');
            return;
        }
        
        const newEvent = {
            id: currentEventId++,
            title,
            start: new Date(start).toISOString(),
            end: new Date(end).toISOString(),
            backgroundColor: color,
            borderColor: color,
            description
        };
        
        // Send data to PHP
        fetch('save_event.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                title,
                start,
                end,
                color,
                description
            })
        })
        .then(res => res.json())
        .then(response => {
            console.log('Server Response:', response);
            if (response.status === 'success') {
                events.push(newEvent);
                saveEvents();
                refreshCalendar();
                renderEventsList();
                eventForm.reset();
            } else {
                alert('Error saving to database: ' + response.message);
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Network error. Could not save event.');
        });
    }); // Closing brace for eventForm.addEventListener
    
    // Clear form button
    clearFormBtn.addEventListener('click', function() {
        eventForm.reset();
    });
    
    // Close modal
    closeModal.addEventListener('click', function() {
        eventModal.classList.add('hidden');
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === eventModal) {
            eventModal.classList.add('hidden');
        }
    });
});

// Function to render the events list
function renderEventsList() {
    eventsList.innerHTML = '';
    
    if (events.length === 0) {
        eventsList.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No events added yet</td></tr>';
        return;
    }
    
    // Sort events by start date
    const sortedEvents = [...events].sort((a, b) => new Date(a.start) - new Date(b.start));
    
    sortedEvents.forEach(event => {
        const row = document.createElement('tr');
        
        // Event title with color indicator
        const titleCell = document.createElement('td');
        titleCell.className = 'px-6 py-4 whitespace-nowrap';
        const titleContent = document.createElement('div');
        titleContent.className = 'flex items-center';
        const colorIndicator = document.createElement('span');
        colorIndicator.className = 'event-color-indicator';
        colorIndicator.style.backgroundColor = event.backgroundColor;
        const titleText = document.createElement('span');
        titleText.textContent = event.title;
        titleContent.appendChild(colorIndicator);
        titleContent.appendChild(titleText);
        titleCell.appendChild(titleContent);
        
        // Start date
        const startCell = document.createElement('td');
        startCell.className = 'px-6 py-4 whitespace-nowrap';
        startCell.textContent = formatDateForDisplay(event.start);
        
        // End date
        const endCell = document.createElement('td');
        endCell.className = 'px-6 py-4 whitespace-nowrap';
        endCell.textContent = formatDateForDisplay(event.end);
        
        // Description
        const descCell = document.createElement('td');
        descCell.className = 'px-6 py-4';
        descCell.textContent = event.description || '-';
        
        // Actions
        const actionsCell = document.createElement('td');
        actionsCell.className = 'px-6 py-4 whitespace-nowrap text-right text-sm font-medium';
        
        const viewBtn = document.createElement('button');
        viewBtn.className = 'text-indigo-600 hover:text-indigo-900 mr-4';
        viewBtn.textContent = 'View';
        viewBtn.addEventListener('click', () => showEventModal(event));
        
        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'text-red-600 hover:text-red-900';
        deleteBtn.textContent = 'Delete';
        deleteBtn.addEventListener('click', () => deleteEvent(event.id));
        
        actionsCell.appendChild(viewBtn);
        actionsCell.appendChild(deleteBtn);
        
        row.appendChild(titleCell);
        row.appendChild(startCell);
        row.appendChild(endCell);
        row.appendChild(descCell);
        row.appendChild(actionsCell);
        
        eventsList.appendChild(row);
    });
}

// Function to show event modal
function showEventModal(event) {
    const startDate = new Date(event.start);
    const endDate = new Date(event.end);
    
    modalContent.innerHTML = `
        <div class="space-y-3">
            <div class="flex items-center">
                <span class="event-color-indicator" style="background-color: ${event.backgroundColor}"></span>
                <h4 class="text-xl font-semibold">${event.title}</h4>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Start</p>
                    <p>${formatDateForDisplay(event.start)}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">End</p>
                    <p>${formatDateForDisplay(event.end)}</p>
                </div>
            </div>
            
            <div>
                <p class="text-sm text-gray-500">Description</p>
                <p class="mt-1">${event.description || 'No description provided'}</p>
            </div>
            
            <div class="flex justify-end space-x-4 mt-6">
                <button id="editEventBtn" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-300">
                    Edit
                </button>
                <button id="deleteEventBtn" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition duration-300">
                    Delete
                </button>
            </div>
        </div>
    `;
    
    // Show modal
    eventModal.classList.remove('hidden');
    
    // Add event listeners for modal buttons
    document.getElementById('editEventBtn').addEventListener('click', () => {
        editEvent(event);
        eventModal.classList.add('hidden');
    });
    
    document.getElementById('deleteEventBtn').addEventListener('click', () => {
        deleteEvent(event.id);
        eventModal.classList.add('hidden');
    });
}

// Function to edit an event
function editEvent(event) {
    // Populate form with event details
    document.getElementById('eventTitle').value = event.title;
    document.getElementById('eventStart').value = formatDateForInput(event.start);
    document.getElementById('eventEnd').value = formatDateForInput(event.end);
    document.getElementById('eventColor').value = event.backgroundColor;
    document.getElementById('eventDescription').value = event.description || '';
    
    // Delete the old event
    deleteEvent(event.id, true); // The second parameter prevents refresh to avoid flickering
    
    // Scroll to form
    document.querySelector('.lg\\:col-span-1').scrollIntoView({ behavior: 'smooth' });
}

// Function to delete an event
function deleteEvent(id, skipRefresh = false) {
    events = events.filter(event => event.id !== id);
    saveEvents();
    
    if (!skipRefresh) {
        refreshCalendar();
        renderEventsList();
    }
}

// Function to save events to local storage
function saveEvents() {
    localStorage.setItem('calendarEvents', JSON.stringify(events));
    localStorage.setItem('currentEventId', JSON.stringify(currentEventId));
}

// Helper function to format date for input fields
function formatDateForInput(dateStr) {
    const date = new Date(dateStr);
    
    // Format: YYYY-MM-DDTHH:MM
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

// Helper function to format date for display
function formatDateForDisplay(dateStr) {
    const date = new Date(dateStr);
    const options = { 
        weekday: 'short', 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit', 
        minute: '2-digit'
    };
    
    return date.toLocaleDateString('en-US', options);
}

// Helper function to format date
function formatDate(date) {
    return date.toISOString();
} 