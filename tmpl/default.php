<?php

/**
 * @package     Cybersalt.Module
 * @subpackage  mod_worldclocks
 *
 * @copyright   (C) 2025 Cybersalt. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var array $clocks */
/** @var string $displayStyle */
/** @var string $timeFormat */
/** @var bool $showSeconds */
/** @var bool $showDate */
/** @var int $moduleId */
/** @var bool $showLocalTime */

// Don't render if no clocks selected and local time is not enabled
if (empty($clocks) && empty($showLocalTime)) {
    return;
}

$moduleClass = $params->get('moduleclass_sfx', '');
?>
<div id="mod-worldclocks-<?php echo $moduleId; ?>"
     class="mod-worldclocks mod-worldclocks--<?php echo htmlspecialchars($displayStyle); ?> <?php echo htmlspecialchars($moduleClass); ?>"
     data-module-id="<?php echo $moduleId; ?>">

    <div class="worldclocks-container">
        <?php foreach ($clocks as $clock) : ?>
            <div class="worldclock"
                 data-timezone="<?php echo htmlspecialchars($clock['timezone']); ?>">

                <?php if ($displayStyle === 'analog') : ?>
                    <div class="worldclock__analog">
                        <div class="worldclock__face">
                            <div class="worldclock__hand worldclock__hand--hour"></div>
                            <div class="worldclock__hand worldclock__hand--minute"></div>
                            <?php if ($showSeconds) : ?>
                                <div class="worldclock__hand worldclock__hand--second"></div>
                            <?php endif; ?>
                            <div class="worldclock__center"></div>
                            <?php for ($i = 1; $i <= 12; $i++) : ?>
                                <span class="worldclock__number worldclock__number--<?php echo $i; ?>"><?php echo $i; ?></span>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($displayStyle === 'digital' || $displayStyle === 'text') : ?>
                    <div class="worldclock__time">
                        <span class="worldclock__hours">--</span>
                        <span class="worldclock__separator">:</span>
                        <span class="worldclock__minutes">--</span>
                        <?php if ($showSeconds) : ?>
                            <span class="worldclock__separator">:</span>
                            <span class="worldclock__seconds">--</span>
                        <?php endif; ?>
                        <?php if ($timeFormat === '12') : ?>
                            <span class="worldclock__period">--</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($showDate) : ?>
                    <div class="worldclock__date">--</div>
                <?php endif; ?>

                <div class="worldclock__name"><?php echo htmlspecialchars($clock['name']); ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
