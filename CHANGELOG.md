# Changelog

All notable changes to the World Clocks module will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.1] - 2025-12-18

### Fixed
- **Preset Location Dropdown**: Changed from `list` with `fancy-select` to `groupedlist` type for proper section headers
  - "National Capitals" and "States & Provinces" now display as proper `<optgroup>` headers
  - Disabled options with empty values didn't work correctly with Choices.js

## [1.1.0] - 2025-12-18

### New Features
- **Visitor's Local Time Clock**: Auto-detect and display visitor's local timezone
  - Position at top or bottom of clock list
  - Custom label or auto-detect from timezone identifier
  - Uses browser's `Intl.DateTimeFormat().resolvedOptions().timeZone`

- **Unified Clock Selector**: Single sortable list combining all location types
  - Drag-and-drop reordering for all clocks
  - Preset locations (capitals and cities) with grouped dropdown
  - Custom locations with manual timezone and label
  - Uses subform with `buttons="add,remove,move"` for full ordering control

- **Live Preview in Admin**: Real-time style preview while editing
  - MutationObserver watches for DOM changes
  - Click events on form inputs trigger updates
  - Fallback polling ensures updates even without events

- **Style-Specific Customization**: Separate styling options per display type
  - Text list: city/time/date fonts, colors, border color
  - Digital cards: fonts, colors, background, border, radius
  - Analog clocks: size, face/border colors, hand colors, number styling

### Changed
- Combined separate capitals/regional_cities/clock_order fields into unified `clocks` subform
- Values now encoded as `timezone|langKey` format for proper translation support
- Local time shows full timezone identifier (e.g., "America/New_York") when no custom label set

### Fixed
- Local time clock not displaying (wrong CSS selector `.worldclocks` instead of `.worldclocks-container`)
- Template early return when `$clocks` empty but local time enabled
- Local time label appearing before date in element order
- Analog clock only showing 4 numbers instead of all 12

## [1.0.0] - 2025-12-17

### New Features
- **Initial Release**: Joomla 5 native module for displaying world clocks
- **Three Display Styles**: Text list, digital cards, analog clocks
- **50+ World Capitals**: Pre-configured capitals across all continents
- **Time Format Options**: 12-hour and 24-hour formats
- **Show/Hide Options**: Seconds and date display toggles
- **Custom CSS Field**: Per-instance styling capability
- **Real-Time Updates**: JavaScript-powered clock updates
- **Full Translation Support**: Language system integration

### Technical
- Built using Joomla 5's modern Dispatcher pattern
- Dependency injection via `services/provider.php`
- WebAssetManager for CSS/JS registration
- No entry point file required
