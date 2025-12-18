/**
 * @package     Cybersalt.Module
 * @subpackage  mod_worldclocks
 *
 * @copyright   (C) 2025 Cybersalt. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

(function () {
    'use strict';

    /**
     * WorldClocks class - handles real-time clock updates
     */
    class WorldClocksModule {
        /**
         * Constructor
         * @param {Object} config - Module configuration
         */
        constructor(config) {
            this.config = config;
            this.container = document.getElementById('mod-worldclocks-' + config.moduleId);
            this.intervalId = null;

            if (this.container) {
                this.init();
            }
        }

        /**
         * Initialize the clocks
         */
        init() {
            // Add local time clock if enabled
            if (this.config.showLocalTime) {
                this.addLocalTimeClock();
            }

            this.updateAllClocks();
            this.startInterval();
        }

        /**
         * Get the visitor's local timezone
         * @returns {string}
         */
        getLocalTimezone() {
            try {
                return Intl.DateTimeFormat().resolvedOptions().timeZone;
            } catch (e) {
                return 'UTC';
            }
        }

        /**
         * Get a friendly timezone name from the IANA timezone
         * @param {string} timezone - IANA timezone identifier
         * @returns {string}
         */
        getTimezoneFriendlyName(timezone) {
            // Extract city name from timezone (e.g., "America/New_York" -> "New York")
            const parts = timezone.split('/');
            const city = parts[parts.length - 1].replace(/_/g, ' ');
            return city;
        }

        /**
         * Add local time clock element dynamically
         */
        addLocalTimeClock() {
            const localTimezone = this.getLocalTimezone();
            // Use custom label if provided, otherwise show full timezone identifier
            const label = this.config.localTimeLabel || localTimezone;

            // Create the clock element based on display style
            const clockEl = this.createClockElement(localTimezone, label, true);

            // Find where to insert it
            const clocksContainer = this.container.querySelector('.worldclocks-container');
            if (!clocksContainer) return;

            if (this.config.localTimePosition === 'first') {
                clocksContainer.insertBefore(clockEl, clocksContainer.firstChild);
            } else {
                clocksContainer.appendChild(clockEl);
            }
        }

        /**
         * Create a clock HTML element
         * @param {string} timezone - Timezone identifier
         * @param {string} name - Display name
         * @param {boolean} isLocal - Whether this is the local time clock
         * @returns {HTMLElement}
         */
        createClockElement(timezone, name, isLocal = false) {
            const div = document.createElement('div');
            div.className = 'worldclock' + (isLocal ? ' worldclock--local' : '');
            div.dataset.timezone = timezone;

            if (this.config.displayStyle === 'analog') {
                div.innerHTML = this.getAnalogClockHTML(name);
            } else {
                div.innerHTML = this.getDigitalClockHTML(name);
            }

            return div;
        }

        /**
         * Get HTML for digital/text style clock
         * @param {string} name - Clock label
         * @returns {string}
         */
        getDigitalClockHTML(name) {
            let html = '<div class="worldclock__time">';
            html += '<span class="worldclock__hours">--</span>';
            html += '<span class="worldclock__separator">:</span>';
            html += '<span class="worldclock__minutes">--</span>';

            if (this.config.showSeconds) {
                html += '<span class="worldclock__separator">:</span>';
                html += '<span class="worldclock__seconds">--</span>';
            }

            if (this.config.timeFormat === '12') {
                html += '<span class="worldclock__period"></span>';
            }

            html += '</div>';

            if (this.config.showDate) {
                html += '<div class="worldclock__date"></div>';
            }

            html += '<div class="worldclock__name">' + this.escapeHtml(name) + '</div>';

            return html;
        }

        /**
         * Get HTML for analog style clock
         * @param {string} name - Clock label
         * @returns {string}
         */
        getAnalogClockHTML(name) {
            let html = '<div class="worldclock__analog">';
            html += '<div class="worldclock__face">';
            html += '<div class="worldclock__hand worldclock__hand--hour"></div>';
            html += '<div class="worldclock__hand worldclock__hand--minute"></div>';

            if (this.config.showSeconds) {
                html += '<div class="worldclock__hand worldclock__hand--second"></div>';
            }

            html += '<div class="worldclock__center"></div>';

            // Add all 12 numbers like the PHP template
            for (let i = 1; i <= 12; i++) {
                html += '<span class="worldclock__number worldclock__number--' + i + '">' + i + '</span>';
            }

            html += '</div></div>';

            if (this.config.showDate) {
                html += '<div class="worldclock__date"></div>';
            }

            html += '<div class="worldclock__name">' + this.escapeHtml(name) + '</div>';

            return html;
        }

        /**
         * Escape HTML special characters
         * @param {string} str - String to escape
         * @returns {string}
         */
        escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        /**
         * Start the update interval
         */
        startInterval() {
            // Update every second if showing seconds, otherwise every minute
            const interval = this.config.showSeconds ? 1000 : 60000;
            this.intervalId = setInterval(() => this.updateAllClocks(), interval);
        }

        /**
         * Update all clocks in the module
         */
        updateAllClocks() {
            const clockElements = this.container.querySelectorAll('.worldclock');

            clockElements.forEach((element) => {
                const timezone = element.dataset.timezone;
                this.updateClock(element, timezone);
            });
        }

        /**
         * Update a single clock
         * @param {HTMLElement} element - Clock element
         * @param {string} timezone - Timezone identifier
         */
        updateClock(element, timezone) {
            const now = new Date();
            const options = {
                timeZone: timezone,
                hour: '2-digit',
                minute: '2-digit',
                hour12: this.config.timeFormat === '12'
            };

            if (this.config.showSeconds) {
                options.second = '2-digit';
            }

            try {
                const formatter = new Intl.DateTimeFormat('en-US', options);
                const parts = formatter.formatToParts(now);

                const timeParts = {};
                parts.forEach(part => {
                    timeParts[part.type] = part.value;
                });

                if (this.config.displayStyle === 'analog') {
                    this.updateAnalogClock(element, timezone);
                } else {
                    this.updateDigitalClock(element, timeParts);
                }

                if (this.config.showDate) {
                    this.updateDate(element, timezone);
                }
            } catch (error) {
                console.error('WorldClocks: Error updating clock for timezone ' + timezone, error);
            }
        }

        /**
         * Update digital/text clock display
         * @param {HTMLElement} element - Clock element
         * @param {Object} timeParts - Time parts object
         */
        updateDigitalClock(element, timeParts) {
            const hoursEl = element.querySelector('.worldclock__hours');
            const minutesEl = element.querySelector('.worldclock__minutes');
            const secondsEl = element.querySelector('.worldclock__seconds');
            const periodEl = element.querySelector('.worldclock__period');

            if (hoursEl) {
                hoursEl.textContent = timeParts.hour || '--';
            }
            if (minutesEl) {
                minutesEl.textContent = timeParts.minute || '--';
            }
            if (secondsEl && timeParts.second) {
                secondsEl.textContent = timeParts.second;
            }
            if (periodEl && timeParts.dayPeriod) {
                periodEl.textContent = timeParts.dayPeriod;
            }
        }

        /**
         * Update analog clock display
         * @param {HTMLElement} element - Clock element
         * @param {string} timezone - Timezone identifier
         */
        updateAnalogClock(element, timezone) {
            const now = new Date();

            // Get time in the specific timezone
            const timeString = now.toLocaleString('en-US', {
                timeZone: timezone,
                hour: 'numeric',
                minute: 'numeric',
                second: 'numeric',
                hour12: false
            });

            const [hours, minutes, seconds] = timeString.split(':').map(Number);

            // Calculate rotation angles
            const secondDegrees = (seconds / 60) * 360;
            const minuteDegrees = ((minutes + seconds / 60) / 60) * 360;
            const hourDegrees = ((hours % 12 + minutes / 60) / 12) * 360;

            // Apply rotations
            const hourHand = element.querySelector('.worldclock__hand--hour');
            const minuteHand = element.querySelector('.worldclock__hand--minute');
            const secondHand = element.querySelector('.worldclock__hand--second');

            if (hourHand) {
                hourHand.style.transform = `rotate(${hourDegrees}deg)`;
            }
            if (minuteHand) {
                minuteHand.style.transform = `rotate(${minuteDegrees}deg)`;
            }
            if (secondHand) {
                secondHand.style.transform = `rotate(${secondDegrees}deg)`;
            }
        }

        /**
         * Update date display
         * @param {HTMLElement} element - Clock element
         * @param {string} timezone - Timezone identifier
         */
        updateDate(element, timezone) {
            const dateEl = element.querySelector('.worldclock__date');
            if (!dateEl) return;

            const now = new Date();
            const dateFormatter = new Intl.DateTimeFormat('en-US', {
                timeZone: timezone,
                weekday: 'short',
                month: 'short',
                day: 'numeric'
            });

            dateEl.textContent = dateFormatter.format(now);
        }

        /**
         * Destroy the module instance
         */
        destroy() {
            if (this.intervalId) {
                clearInterval(this.intervalId);
                this.intervalId = null;
            }
        }
    }

    /**
     * Initialize all WorldClocks modules on the page
     */
    function initWorldClocks() {
        if (!window.WorldClocks) {
            return;
        }

        Object.keys(window.WorldClocks).forEach((key) => {
            const config = window.WorldClocks[key];
            if (config && config.moduleId) {
                new WorldClocksModule(config);
            }
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWorldClocks);
    } else {
        initWorldClocks();
    }
})();
