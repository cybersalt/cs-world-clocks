<?php

/**
 * @package     Cybersalt.Module
 * @subpackage  mod_worldclocks
 *
 * @copyright   (C) 2025 Cybersalt. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

namespace Cybersalt\Module\WorldClocks\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\WebAsset\WebAssetManager;

/**
 * Dispatcher class for mod_worldclocks
 */
class Dispatcher extends AbstractModuleDispatcher
{
    /**
     * Returns the layout data.
     *
     * @return  array
     */
    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();

        $params = $data['params'];
        $module = $data['module'];

        // Build clock data
        $clocks = $this->buildClockList($params);

        $data['clocks'] = $clocks;
        $data['displayStyle'] = $params->get('display_style', 'digital');
        $data['timeFormat'] = $params->get('time_format', '12');
        $data['showSeconds'] = (bool) $params->get('show_seconds', 1);
        $data['showDate'] = (bool) $params->get('show_date', 0);
        $data['moduleId'] = $module->id;
        $data['customCss'] = $params->get('custom_css', '');

        // Local time settings
        $data['showLocalTime'] = (bool) $params->get('show_local_time', 0);
        $data['localTimePosition'] = $params->get('local_time_position', 'first');
        $data['localTimeLabel'] = $params->get('local_time_label', '');

        // Register assets
        $this->registerAssets($data);

        return $data;
    }

    /**
     * Build the list of clocks from all sources
     *
     * @param   object  $params  Module parameters
     *
     * @return  array
     */
    protected function buildClockList($params): array
    {
        $clocks = [];
        $useCustomOnly = (bool) $params->get('use_custom_order', 0);

        if ($useCustomOnly) {
            // Only use custom clocks from subform
            $customClocks = $params->get('clock_order', []);

            if (!empty($customClocks)) {
                foreach ($customClocks as $clock) {
                    if (!empty($clock->timezone) && !empty($clock->label)) {
                        $clocks[] = [
                            'timezone' => $clock->timezone,
                            'name' => $clock->label,
                            'nameKey' => ''
                        ];
                    }
                }
            }
        } else {
            // Add selected capitals from subform (ordered)
            $capitals = $params->get('capitals', []);

            if (!empty($capitals)) {
                foreach ($capitals as $capital) {
                    if (!empty($capital->city)) {
                        // Value format: timezone|langKey
                        $parts = explode('|', $capital->city, 2);
                        if (count($parts) === 2) {
                            $clocks[] = [
                                'timezone' => $parts[0],
                                'name' => Text::_($parts[1]),
                                'nameKey' => $parts[1]
                            ];
                        }
                    }
                }
            }

            // Add selected regional cities from subform (ordered)
            $regionalCities = $params->get('regional_cities', []);

            if (!empty($regionalCities)) {
                foreach ($regionalCities as $city) {
                    if (!empty($city->city)) {
                        // Value format: timezone|langKey
                        $parts = explode('|', $city->city, 2);
                        if (count($parts) === 2) {
                            $clocks[] = [
                                'timezone' => $parts[0],
                                'name' => Text::_($parts[1]),
                                'nameKey' => $parts[1]
                            ];
                        }
                    }
                }
            }

            // Add custom clocks from subform
            $customClocks = $params->get('clock_order', []);

            if (!empty($customClocks)) {
                foreach ($customClocks as $clock) {
                    if (!empty($clock->timezone) && !empty($clock->label)) {
                        $clocks[] = [
                            'timezone' => $clock->timezone,
                            'name' => $clock->label,
                            'nameKey' => ''
                        ];
                    }
                }
            }
        }

        return $clocks;
    }

    /**
     * Register CSS and JavaScript assets
     *
     * @param   array  $data  The layout data
     *
     * @return  void
     */
    protected function registerAssets(array $data): void
    {
        /** @var WebAssetManager $wa */
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();

        $wa->registerAndUseStyle(
            'mod_worldclocks',
            'media/mod_worldclocks/css/worldclocks.css',
            ['version' => 'auto']
        );

        $wa->registerAndUseScript(
            'mod_worldclocks',
            'media/mod_worldclocks/js/worldclocks.js',
            ['version' => 'auto'],
            ['defer' => true]
        );

        // Pass configuration to JavaScript
        $config = [
            'moduleId' => $data['moduleId'],
            'clocks' => $data['clocks'],
            'displayStyle' => $data['displayStyle'],
            'timeFormat' => $data['timeFormat'],
            'showSeconds' => $data['showSeconds'],
            'showDate' => $data['showDate'],
            'showLocalTime' => $data['showLocalTime'],
            'localTimePosition' => $data['localTimePosition'],
            'localTimeLabel' => $data['localTimeLabel']
        ];

        $wa->addInlineScript(
            'window.WorldClocks = window.WorldClocks || {};'
            . 'window.WorldClocks["module' . $data['moduleId'] . '"] = ' . json_encode($config) . ';',
            ['position' => 'before'],
            [],
            ['mod_worldclocks']
        );

        // Add styling options as inline CSS
        $this->addStylingCss($wa, $data);

        // Add custom CSS if provided (output directly, user provides complete rules)
        if (!empty($data['customCss'])) {
            $wa->addInlineStyle($data['customCss']);
        }
    }

    /**
     * Generate and add CSS from styling options
     *
     * @param   WebAssetManager  $wa    The web asset manager
     * @param   array            $data  The layout data
     *
     * @return  void
     */
    protected function addStylingCss(WebAssetManager $wa, array $data): void
    {
        $params = $data['params'];
        $moduleId = $data['moduleId'];
        $displayStyle = $data['displayStyle'];
        $selector = '#mod-worldclocks-' . $moduleId;

        $css = [];

        // Get style-specific prefix
        $prefix = $displayStyle . '_';

        // City/Name styling (style-specific)
        $cityStyles = [];
        $cityFontSize = $params->get($prefix . 'city_font_size', '');
        $cityFontWeight = $params->get($prefix . 'city_font_weight', '');
        $cityColor = $params->get($prefix . 'city_color', '');

        if (!empty($cityFontSize)) {
            $cityStyles[] = 'font-size: ' . htmlspecialchars($cityFontSize);
        }
        if (!empty($cityFontWeight)) {
            $cityStyles[] = 'font-weight: ' . htmlspecialchars($cityFontWeight);
        }
        if (!empty($cityColor)) {
            $cityStyles[] = 'color: ' . htmlspecialchars($cityColor);
        }
        if (!empty($cityStyles)) {
            $css[] = $selector . ' .worldclock__name { ' . implode('; ', $cityStyles) . '; }';
        }

        // Time styling (for text and digital styles)
        if ($displayStyle !== 'analog') {
            $timeStyles = [];
            $timeFontSize = $params->get($prefix . 'time_font_size', '');
            $timeFontWeight = $params->get($prefix . 'time_font_weight', '');
            $timeColor = $params->get($prefix . 'time_color', '');

            if (!empty($timeFontSize)) {
                $timeStyles[] = 'font-size: ' . htmlspecialchars($timeFontSize);
            }
            if (!empty($timeFontWeight)) {
                $timeStyles[] = 'font-weight: ' . htmlspecialchars($timeFontWeight);
            }
            if (!empty($timeColor)) {
                $timeStyles[] = 'color: ' . htmlspecialchars($timeColor);
            }
            if (!empty($timeStyles)) {
                $css[] = $selector . ' .worldclock__time { ' . implode('; ', $timeStyles) . '; }';
            }
        }

        // Date styling (style-specific)
        $dateStyles = [];
        $dateFontSize = $params->get($prefix . 'date_font_size', '');
        $dateColor = $params->get($prefix . 'date_color', '');

        if (!empty($dateFontSize)) {
            $dateStyles[] = 'font-size: ' . htmlspecialchars($dateFontSize);
        }
        if (!empty($dateColor)) {
            $dateStyles[] = 'color: ' . htmlspecialchars($dateColor);
        }
        if (!empty($dateStyles)) {
            $css[] = $selector . ' .worldclock__date { ' . implode('; ', $dateStyles) . '; }';
        }

        // Text style specific
        if ($displayStyle === 'text') {
            $textBorderColor = $params->get('text_border_color', '');
            if (!empty($textBorderColor)) {
                $css[] = $selector . ' .worldclock { border-color: ' . htmlspecialchars($textBorderColor) . '; }';
            }
        }

        // Digital style specific
        if ($displayStyle === 'digital') {
            $cardStyles = [];
            $cardBg = $params->get('digital_card_bg', '');
            $cardBorder = $params->get('digital_card_border', '');
            $cardRadius = $params->get('digital_card_radius', '');

            if (!empty($cardBg)) {
                $cardStyles[] = 'background: ' . htmlspecialchars($cardBg);
            }
            if (!empty($cardBorder)) {
                $cardStyles[] = 'border-color: ' . htmlspecialchars($cardBorder);
            }
            if (!empty($cardRadius)) {
                $cardStyles[] = 'border-radius: ' . htmlspecialchars($cardRadius);
            }
            if (!empty($cardStyles)) {
                $css[] = $selector . ' .worldclock { ' . implode('; ', $cardStyles) . '; }';
            }
        }

        // Analog style specific
        if ($displayStyle === 'analog') {
            $analogSize = $params->get('analog_size', '');
            $analogFaceColor = $params->get('analog_face_color', '');
            $analogBorderColor = $params->get('analog_border_color', '');
            $analogHandColor = $params->get('analog_hand_color', '');
            $analogSecondHandColor = $params->get('analog_second_hand_color', '');
            $analogCenterColor = $params->get('analog_center_color', '');
            $analogNumberColor = $params->get('analog_number_color', '');
            $analogNumberFontSize = $params->get('analog_number_font_size', '');

            // Clock size
            if (!empty($analogSize)) {
                $css[] = $selector . ' .worldclock__analog { width: ' . htmlspecialchars($analogSize) . '; height: ' . htmlspecialchars($analogSize) . '; }';
            }

            // Clock face
            $faceStyles = [];
            if (!empty($analogFaceColor)) {
                $faceStyles[] = 'background: ' . htmlspecialchars($analogFaceColor);
            }
            if (!empty($analogBorderColor)) {
                $faceStyles[] = 'border-color: ' . htmlspecialchars($analogBorderColor);
            }
            if (!empty($faceStyles)) {
                $css[] = $selector . ' .worldclock__face { ' . implode('; ', $faceStyles) . '; }';
            }

            // Hour and minute hands
            if (!empty($analogHandColor)) {
                $css[] = $selector . ' .worldclock__hand--hour, ' . $selector . ' .worldclock__hand--minute { background: ' . htmlspecialchars($analogHandColor) . '; }';
            }

            // Second hand
            if (!empty($analogSecondHandColor)) {
                $css[] = $selector . ' .worldclock__hand--second { background: ' . htmlspecialchars($analogSecondHandColor) . '; }';
            }

            // Center dot
            if (!empty($analogCenterColor)) {
                $css[] = $selector . ' .worldclock__center { background: ' . htmlspecialchars($analogCenterColor) . '; }';
            }

            // Numbers
            $numberStyles = [];
            if (!empty($analogNumberColor)) {
                $numberStyles[] = 'color: ' . htmlspecialchars($analogNumberColor);
            }
            if (!empty($analogNumberFontSize)) {
                $numberStyles[] = 'font-size: ' . htmlspecialchars($analogNumberFontSize);
            }
            if (!empty($numberStyles)) {
                $css[] = $selector . ' .worldclock__number { ' . implode('; ', $numberStyles) . '; }';
            }
        }

        // Add combined CSS if any styles were set
        if (!empty($css)) {
            $wa->addInlineStyle(implode("\n", $css));
        }
    }
}
