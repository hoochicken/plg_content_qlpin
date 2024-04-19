<?php
/**
 * @package        plg_qlpin
 * @copyright    Copyright (C) 2024 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

//no direct access
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var array $states */
/** @var \Joomla\CMS\HTML\Registry $params */

defined('_JEXEC') or die ('Restricted Access');
Factory::getDocument()->addStyleSheet(Uri::base() . '/plugins/content/qlpin/css/qlpin.css');
?>
<div id="<?php echo $states['id']; ?>" style="float:<?php echo $params->get('horizontal', 'left'); ?>;"
     class="qlpin <?php echo $params->get('span', 'span3'); ?> <?php echo $states['class']; ?>">
    <?php if (!empty(trim($params->get('contentHeadline')))): ?>
        <h3><?php echo Text::_($params->get('contentHeadline')); ?></h3><?php endif; ?>
    <div class="text">
        <?php echo Text::_($params->get('contentText', '')); ?>
    </div>
    <div class="code">
        <?php $this->parsePhpViaFile($this->params->get('contentCode')); ?>
    </div>
</div>