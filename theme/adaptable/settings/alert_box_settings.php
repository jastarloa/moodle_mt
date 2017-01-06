<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version details
 *
 * @package    theme_adaptable
 * @copyright 2015 Jeremy Hopkins (Coventry University)
 * @copyright 2015 Fernando Acedo (3-bits.com)
 * @copyright 2016 COMETE (Paris Nanterre University)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die;

// Alert Section.
$temp = new admin_settingpage('theme_adaptable_frontpage_alert_box_settings', get_string('frontpagealertboxssettings', 'theme_adaptable'));
$temp->add(new admin_setting_heading('theme_adaptable_alert_box_settings', get_string('alertcolorsheading', 'theme_adaptable'),
format_text(get_string('alertcolorsheadingdesc', 'theme_adaptable'), FORMAT_MARKDOWN)));



$name = 'theme_adaptable/alertcolorinfo';
$title = get_string('alertcolorinfo', 'theme_adaptable');
$description = get_string('alertcolorinfodesc', 'theme_adaptable');
$previewconfig = null;
$setting = new admin_setting_configcolourpicker($name, $title, $description, '#3a87ad', $previewconfig);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

$name = 'theme_adaptable/alertbackgroundcolorinfo';
$title = get_string('alertbackgroundcolorinfo', 'theme_adaptable');
$description = get_string('alertbackgroundcolorinfodesc', 'theme_adaptable');
$previewconfig = null;
$setting = new admin_setting_configcolourpicker($name, $title, $description, '#d9edf7', $previewconfig);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

$name = 'theme_adaptable/alertbordercolorinfo';
$title = get_string('alertbordercolorinfo', 'theme_adaptable');
$description = get_string('alertbordercolorinfodesc', 'theme_adaptable');
$previewconfig = null;
$setting = new admin_setting_configcolourpicker($name, $title, $description, '#bce8f1', $previewconfig);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

$name = 'theme_adaptable/alerticoninfo';
$title = get_string('alerticoninfo', 'theme_adaptable');
$description = get_string('alerticoninfodesc', 'theme_adaptable');
$setting = new admin_setting_configtext($name, $title, $description, 'info-circle');
$temp->add($setting);



$name = 'theme_adaptable/alertcolorsuccess';
$title = get_string('alertcolorsuccess', 'theme_adaptable');
$description = get_string('alertcolorsuccessdesc', 'theme_adaptable');
$previewconfig = null;
$setting = new admin_setting_configcolourpicker($name, $title, $description, '#468847', $previewconfig);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

$name = 'theme_adaptable/alertbackgroundcolorsuccess';
$title = get_string('alertbackgroundcolorsuccess', 'theme_adaptable');
$description = get_string('alertbackgroundcolorsuccessdesc', 'theme_adaptable');
$previewconfig = null;
$setting = new admin_setting_configcolourpicker($name, $title, $description, '#dff0d8', $previewconfig);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

$name = 'theme_adaptable/alertbordercolorsuccess';
$title = get_string('alertbordercolorsuccess', 'theme_adaptable');
$description = get_string('alertbordercolorsuccessdesc', 'theme_adaptable');
$previewconfig = null;
$setting = new admin_setting_configcolourpicker($name, $title, $description, '#d6e9c6', $previewconfig);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

$name = 'theme_adaptable/alerticonsuccess';
$title = get_string('alerticonsuccess', 'theme_adaptable');
$description = get_string('alerticonsuccessdesc', 'theme_adaptable');
$setting = new admin_setting_configtext($name, $title, $description, 'bullhorn');
$temp->add($setting);



$name = 'theme_adaptable/alertcolorwarning';
$title = get_string('alertcolorwarning', 'theme_adaptable');
$description = get_string('alertcolorwarningdesc', 'theme_adaptable');
$previewconfig = null;
$setting = new admin_setting_configcolourpicker($name, $title, $description, '#8a6d3b', $previewconfig);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

$name = 'theme_adaptable/alertbackgroundcolorwarning';
$title = get_string('alertbackgroundcolorwarning', 'theme_adaptable');
$description = get_string('alertbackgroundcolorwarningdesc', 'theme_adaptable');
$previewconfig = null;
$setting = new admin_setting_configcolourpicker($name, $title, $description, '#fcf8e3', $previewconfig);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

$name = 'theme_adaptable/alertbordercolorwarning';
$title = get_string('alertbordercolorwarning', 'theme_adaptable');
$description = get_string('alertbordercolorwarningdesc', 'theme_adaptable');
$previewconfig = null;
$setting = new admin_setting_configcolourpicker($name, $title, $description, '#fbeed5', $previewconfig);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

$name = 'theme_adaptable/alerticonwarning';
$title = get_string('alerticonwarning', 'theme_adaptable');
$description = get_string('alerticonwarningdesc', 'theme_adaptable');
$setting = new admin_setting_configtext($name, $title, $description, 'exclamation-triangle');
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);



$ADMIN->add('theme_adaptable', $temp);
