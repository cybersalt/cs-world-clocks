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
            this.updateAllClocks();
            this.startInterval();
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
