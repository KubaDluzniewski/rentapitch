import './bootstrap.js';
import 'leaflet/dist/leaflet.css';
import "bootstrap-icons/font/bootstrap-icons.css";
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

document.addEventListener('DOMContentLoaded', function() {
    const pitchId = 1; // ID boiska, które chcesz wyświetlić

    const calendarEl = document.getElementById('calendar');
    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, interactionPlugin],
        events: function(info, successCallback, failureCallback) {
            fetch(`/api/reservations/${pitchId}`)
                .then(response => response.json())
                .then(data => {
                    const events = data.map(item => ({
                        title: 'Zajęte',
                        start: item.start,
                        end: item.end
                    }));
                    successCallback(events);
                })
                .catch(failureCallback);
        }
    });
    calendar.render();
});

/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
