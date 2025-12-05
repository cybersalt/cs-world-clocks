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

        // Get selected capitals
        $capitals = $params->get('capitals', []);

        if (!is_array($capitals)) {
            $capitals = $capitals ? [$capitals] : [];
        }

        // Build clock data
        $clocks = [];
        $capitalNames = $this->getCapitalNames();

        foreach ($capitals as $timezone) {
            if (isset($capitalNames[$timezone])) {
                $clocks[] = [
                    'timezone' => $timezone,
                    'name' => Text::_($capitalNames[$timezone]),
                    'nameKey' => $capitalNames[$timezone]
                ];
            }
        }

        $data['clocks'] = $clocks;
        $data['displayStyle'] = $params->get('display_style', 'digital');
        $data['timeFormat'] = $params->get('time_format', '12');
        $data['showSeconds'] = (bool) $params->get('show_seconds', 1);
        $data['showDate'] = (bool) $params->get('show_date', 0);
        $data['moduleId'] = $module->id;
        $data['customCss'] = $params->get('custom_css', '');

        // Register assets
        $this->registerAssets($data);

        return $data;
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
            'showDate' => $data['showDate']
        ];

        $wa->addInlineScript(
            'window.WorldClocks = window.WorldClocks || {};'
            . 'window.WorldClocks["module' . $data['moduleId'] . '"] = ' . json_encode($config) . ';',
            ['position' => 'before'],
            [],
            ['mod_worldclocks']
        );

        // Add custom CSS if provided
        if (!empty($data['customCss'])) {
            $wa->addInlineStyle(
                '#mod-worldclocks-' . $data['moduleId'] . ' { ' . $data['customCss'] . ' }'
            );
        }
    }

    /**
     * Get mapping of timezone to capital name language keys
     *
     * @return  array
     */
    protected function getCapitalNames(): array
    {
        return [
            'Europe/London' => 'MOD_WORLDCLOCKS_CAPITAL_LONDON',
            'Europe/Paris' => 'MOD_WORLDCLOCKS_CAPITAL_PARIS',
            'Europe/Berlin' => 'MOD_WORLDCLOCKS_CAPITAL_BERLIN',
            'Europe/Rome' => 'MOD_WORLDCLOCKS_CAPITAL_ROME',
            'Europe/Madrid' => 'MOD_WORLDCLOCKS_CAPITAL_MADRID',
            'Europe/Amsterdam' => 'MOD_WORLDCLOCKS_CAPITAL_AMSTERDAM',
            'Europe/Brussels' => 'MOD_WORLDCLOCKS_CAPITAL_BRUSSELS',
            'Europe/Vienna' => 'MOD_WORLDCLOCKS_CAPITAL_VIENNA',
            'Europe/Stockholm' => 'MOD_WORLDCLOCKS_CAPITAL_STOCKHOLM',
            'Europe/Oslo' => 'MOD_WORLDCLOCKS_CAPITAL_OSLO',
            'Europe/Copenhagen' => 'MOD_WORLDCLOCKS_CAPITAL_COPENHAGEN',
            'Europe/Helsinki' => 'MOD_WORLDCLOCKS_CAPITAL_HELSINKI',
            'Europe/Warsaw' => 'MOD_WORLDCLOCKS_CAPITAL_WARSAW',
            'Europe/Prague' => 'MOD_WORLDCLOCKS_CAPITAL_PRAGUE',
            'Europe/Budapest' => 'MOD_WORLDCLOCKS_CAPITAL_BUDAPEST',
            'Europe/Athens' => 'MOD_WORLDCLOCKS_CAPITAL_ATHENS',
            'Europe/Lisbon' => 'MOD_WORLDCLOCKS_CAPITAL_LISBON',
            'Europe/Dublin' => 'MOD_WORLDCLOCKS_CAPITAL_DUBLIN',
            'Europe/Zurich' => 'MOD_WORLDCLOCKS_CAPITAL_BERN',
            'Europe/Moscow' => 'MOD_WORLDCLOCKS_CAPITAL_MOSCOW',
            'Europe/Kiev' => 'MOD_WORLDCLOCKS_CAPITAL_KYIV',
            'America/New_York' => 'MOD_WORLDCLOCKS_CAPITAL_WASHINGTON',
            'America/Toronto' => 'MOD_WORLDCLOCKS_CAPITAL_OTTAWA',
            'America/Mexico_City' => 'MOD_WORLDCLOCKS_CAPITAL_MEXICO_CITY',
            'America/Havana' => 'MOD_WORLDCLOCKS_CAPITAL_HAVANA',
            'America/Panama' => 'MOD_WORLDCLOCKS_CAPITAL_PANAMA_CITY',
            'America/Bogota' => 'MOD_WORLDCLOCKS_CAPITAL_BOGOTA',
            'America/Lima' => 'MOD_WORLDCLOCKS_CAPITAL_LIMA',
            'America/Santiago' => 'MOD_WORLDCLOCKS_CAPITAL_SANTIAGO',
            'America/Sao_Paulo' => 'MOD_WORLDCLOCKS_CAPITAL_BRASILIA',
            'America/Argentina/Buenos_Aires' => 'MOD_WORLDCLOCKS_CAPITAL_BUENOS_AIRES',
            'America/Caracas' => 'MOD_WORLDCLOCKS_CAPITAL_CARACAS',
            'Asia/Tokyo' => 'MOD_WORLDCLOCKS_CAPITAL_TOKYO',
            'Asia/Seoul' => 'MOD_WORLDCLOCKS_CAPITAL_SEOUL',
            'Asia/Shanghai' => 'MOD_WORLDCLOCKS_CAPITAL_BEIJING',
            'Asia/Hong_Kong' => 'MOD_WORLDCLOCKS_CAPITAL_HONG_KONG',
            'Asia/Taipei' => 'MOD_WORLDCLOCKS_CAPITAL_TAIPEI',
            'Asia/Singapore' => 'MOD_WORLDCLOCKS_CAPITAL_SINGAPORE',
            'Asia/Bangkok' => 'MOD_WORLDCLOCKS_CAPITAL_BANGKOK',
            'Asia/Jakarta' => 'MOD_WORLDCLOCKS_CAPITAL_JAKARTA',
            'Asia/Manila' => 'MOD_WORLDCLOCKS_CAPITAL_MANILA',
            'Asia/Kuala_Lumpur' => 'MOD_WORLDCLOCKS_CAPITAL_KUALA_LUMPUR',
            'Asia/Ho_Chi_Minh' => 'MOD_WORLDCLOCKS_CAPITAL_HANOI',
            'Asia/Kolkata' => 'MOD_WORLDCLOCKS_CAPITAL_NEW_DELHI',
            'Asia/Dhaka' => 'MOD_WORLDCLOCKS_CAPITAL_DHAKA',
            'Asia/Karachi' => 'MOD_WORLDCLOCKS_CAPITAL_ISLAMABAD',
            'Asia/Kabul' => 'MOD_WORLDCLOCKS_CAPITAL_KABUL',
            'Asia/Tehran' => 'MOD_WORLDCLOCKS_CAPITAL_TEHRAN',
            'Asia/Baghdad' => 'MOD_WORLDCLOCKS_CAPITAL_BAGHDAD',
            'Asia/Riyadh' => 'MOD_WORLDCLOCKS_CAPITAL_RIYADH',
            'Asia/Dubai' => 'MOD_WORLDCLOCKS_CAPITAL_ABU_DHABI',
            'Asia/Jerusalem' => 'MOD_WORLDCLOCKS_CAPITAL_JERUSALEM',
            'Asia/Beirut' => 'MOD_WORLDCLOCKS_CAPITAL_BEIRUT',
            'Asia/Amman' => 'MOD_WORLDCLOCKS_CAPITAL_AMMAN',
            'Africa/Cairo' => 'MOD_WORLDCLOCKS_CAPITAL_CAIRO',
            'Africa/Johannesburg' => 'MOD_WORLDCLOCKS_CAPITAL_PRETORIA',
            'Africa/Lagos' => 'MOD_WORLDCLOCKS_CAPITAL_ABUJA',
            'Africa/Nairobi' => 'MOD_WORLDCLOCKS_CAPITAL_NAIROBI',
            'Africa/Casablanca' => 'MOD_WORLDCLOCKS_CAPITAL_RABAT',
            'Africa/Tunis' => 'MOD_WORLDCLOCKS_CAPITAL_TUNIS',
            'Africa/Algiers' => 'MOD_WORLDCLOCKS_CAPITAL_ALGIERS',
            'Australia/Sydney' => 'MOD_WORLDCLOCKS_CAPITAL_CANBERRA',
            'Pacific/Auckland' => 'MOD_WORLDCLOCKS_CAPITAL_WELLINGTON',
            'Pacific/Fiji' => 'MOD_WORLDCLOCKS_CAPITAL_SUVA',
        ];
    }
}
