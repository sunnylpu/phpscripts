# Event Calendar Website

A modern and interactive event calendar web application that allows users to create, manage, and view custom events. Built with HTML, CSS, Tailwind CSS, and JavaScript using the FullCalendar library.

## Features

- Interactive full-screen calendar with month, week, and day views
- Create custom events with titles, start/end times, descriptions, and colors
- Drag and drop events to reschedule them
- Resize events to change their duration
- View all your events in a sortable list
- Edit and delete existing events
- Responsive design that works on both desktop and mobile devices
- Local storage integration to save your events between sessions

## How to Use

1. **Open the application**: Simply open the `index.html` file in your web browser.

2. **View the Calendar**: The calendar is displayed in the main section of the page, with the current month shown by default.
   - Use the navigation buttons to move between months, weeks, or days
   - Click on the "Today" button to quickly return to the current date
   - Switch between month, week, and day views using the buttons in the top-right

3. **Add an Event**:
   - Fill out the "Add New Event" form on the right side (or below on mobile)
   - Provide a title, start and end dates/times, and optionally a color and description
   - Click "Add Event" to save your event to the calendar
   - Click "Clear" to reset the form

4. **Interact with Events**:
   - Click on an event in the calendar to view its details
   - Drag events to move them to different dates or times
   - Drag the bottom edge of an event to resize its duration
   - Click directly on a date in the calendar to pre-fill the event form with that date

5. **Manage Events**:
   - View all your events in the "Your Events" table at the bottom
   - Click "View" to see event details
   - Click "Edit" to load the event into the form for editing
   - Click "Delete" to remove an event

## Customization

You can customize the appearance and behavior of the calendar by modifying:

- `css/styles.css` for custom styling
- `js/script.js` for functionality changes

## Requirements

No installation is required! This application runs entirely in your browser using:

- HTML5
- CSS3 and Tailwind CSS (loaded via CDN)
- JavaScript (ES6+)
- FullCalendar library (loaded via CDN)

## Browser Compatibility

This application works best in modern browsers like:
- Chrome
- Firefox
- Safari
- Edge

## License

This project is open source and available for personal and commercial use.

## Acknowledgements

- [FullCalendar](https://fullcalendar.io/) - For the powerful calendar functionality
- [Tailwind CSS](https://tailwindcss.com/) - For the utility-first CSS framework 