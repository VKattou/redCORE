<?php
/**
 * @package     RedRad
 * @subpackage  Html
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDRAD') or die;

/**
 * Utility class for creating HTML Grids
 *
 * @package     RedRad
 * @subpackage  Html
 * @since       1.0
 */
abstract class JHtmlRgrid
{
	/**
	 * Extension name to use in the asset calls
	 * Basically the media/com_xxxxx folder to use
	 */
	const EXTENSION = 'redrad';

	/**
	 * Load the entire bootstrap framework
	 *
	 * @param   mixed  $debug  Is debugging mode on? [optional]
	 *
	 * @return  void
	 */
	public static function main($debug = null)
	{
		JHtml::_('rjquery.framework');

		RHelperAsset::load('redgrid.js', static::EXTENSION);
	}

	/**
	 * Returns an action on a grid
	 *
	 * @param   integer       $i               The row index
	 * @param   string        $task            The task to fire
	 * @param   string|array  $prefix          An optional task prefix or an array of options
	 * @param   string        $text            An optional text to display
	 * @param   string        $active_title    An optional active tooltip to display if $enable is true
	 * @param   string        $inactive_title  An optional inactive tooltip to display if $enable is true
	 * @param   boolean       $tip             An optional setting for tooltip
	 * @param   string        $active_class    An optional active HTML class
	 * @param   string        $inactive_class  An optional inactive HTML class
	 * @param   boolean       $enabled         An optional setting for access control on the action.
	 * @param   boolean       $translate       An optional setting for translation.
	 * @param   string        $checkbox	       An optional prefix for checkboxes.
	 *
	 * @return  string         The Html code
	 */
	public static function action($i, $task, $prefix = '', $text = '', $active_title = '', $inactive_title = '', $tip = false, $active_class = '',
	                              $inactive_class = '', $enabled = true, $translate = true, $checkbox = 'cb')
	{
		if (is_array($prefix))
		{
			$options = $prefix;
			$active_title = array_key_exists('active_title', $options) ? $options['active_title'] : $active_title;
			$inactive_title = array_key_exists('inactive_title', $options) ? $options['inactive_title'] : $inactive_title;
			$tip = array_key_exists('tip', $options) ? $options['tip'] : $tip;
			$active_class = array_key_exists('active_class', $options) ? $options['active_class'] : $active_class;
			$inactive_class = array_key_exists('inactive_class', $options) ? $options['inactive_class'] : $inactive_class;
			$enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
			$translate = array_key_exists('translate', $options) ? $options['translate'] : $translate;
			$checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
			$prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		}

		if ($tip)
		{
			JHtml::_('rbootstrap.tooltip');
		}

		if ($enabled)
		{
			$class = '';

			// Prepare the class.
			if ($active_class === 'plus-sign')
			{
				$class = 'published';
			}

			elseif ($active_class === 'minus-sign')
			{
				$class = 'unpublished';
			}

			$html[] = '<a class="btn btn-small ' . $class . '" ' . ($tip ? 'rel="tooltip"' : '') . '';
			$html[] = ' href="javascript:void(0);" onclick="return listItemTask(\'' . $checkbox . $i . '\',\'' . $prefix . $task . '\')"';
			$html[] = ' title="' . addslashes(htmlspecialchars($translate ? JText::_($active_title) : $active_title, ENT_COMPAT, 'UTF-8')) . '">';
			$html[] = '<i class="icon-' . $active_class . '">';
			$html[] = '</i>';
			$html[] = '</a>';
		}
		else
		{
			$html[] = '<a class="btn btn-micro disabled jgrid" ' . ($tip ? 'rel="tooltip"' : '') . '';
			$html[] = ' title="' . addslashes(htmlspecialchars($translate ? JText::_($inactive_title) : $inactive_title, ENT_COMPAT, 'UTF-8')) . '">';

			if ($active_class == "protected")
			{
				$html[] = '<i class="icon-lock"></i>';
			}
			else
			{
				$html[] = '<i class="icon-' . $inactive_class . '"></i>';
			}

			$html[] = '</a>';
		}

		return implode($html);
	}

	/**
	 * Returns a state on a grid
	 *
	 * @param   array         $states     array of value/state. Each state is an array of the form
	 *                                    (task, text, title,html active class, HTML inactive class)
	 *                                    or ('task'=>task, 'text'=>text, 'active_title'=>active title,
	 *                                    'inactive_title'=>inactive title, 'tip'=>boolean, 'active_class'=>html active class,
	 *                                    'inactive_class'=>html inactive class)
	 * @param   integer       $value      The state value.
	 * @param   integer       $i          The row index
	 * @param   string|array  $prefix     An optional task prefix or an array of options
	 * @param   boolean       $enabled    An optional setting for access control on the action.
	 * @param   boolean       $translate  An optional setting for translation.
	 * @param   string        $checkbox   An optional prefix for checkboxes.
	 *
	 * @return  string       The Html code
	 */
	public static function state($states, $value, $i, $prefix = '', $enabled = true, $translate = true, $checkbox = 'cb')
	{
		if (is_array($prefix))
		{
			$options = $prefix;
			$enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
			$translate = array_key_exists('translate', $options) ? $options['translate'] : $translate;
			$checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
			$prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		}

		$state = JArrayHelper::getValue($states, (int) $value, $states[0]);
		$task = array_key_exists('task', $state) ? $state['task'] : $state[0];
		$text = array_key_exists('text', $state) ? $state['text'] : (array_key_exists(1, $state) ? $state[1] : '');
		$active_title = array_key_exists('active_title', $state) ? $state['active_title'] : (array_key_exists(2, $state) ? $state[2] : '');
		$inactive_title = array_key_exists('inactive_title', $state) ? $state['inactive_title'] : (array_key_exists(3, $state) ? $state[3] : '');
		$tip = array_key_exists('tip', $state) ? $state['tip'] : (array_key_exists(4, $state) ? $state[4] : false);
		$active_class = array_key_exists('active_class', $state) ? $state['active_class'] : (array_key_exists(5, $state) ? $state[5] : '');
		$inactive_class = array_key_exists('inactive_class', $state) ? $state['inactive_class'] : (array_key_exists(6, $state) ? $state[6] : '');

		return self::action(
			$i, $task, $prefix, $text, $active_title, $inactive_title, $tip,
			$active_class, $inactive_class, $enabled, $translate, $checkbox
		);
	}

	/**
	 * Returns a published state on a grid
	 *
	 * @param   integer       $value         The state value.
	 * @param   integer       $i             The row index
	 * @param   string|array  $prefix        An optional task prefix or an array of options
	 * @param   boolean       $enabled       An optional setting for access control on the action.
	 * @param   string        $checkbox      An optional prefix for checkboxes.
	 * @param   string        $publish_up    An optional start publishing date.
	 * @param   string        $publish_down  An optional finish publishing date.
	 *
	 * @return  string  The Html code
	 */
	public static function published($value, $i, $prefix = '', $enabled = true, $checkbox = 'cb', $publish_up = null, $publish_down = null)
	{
		if (is_array($prefix))
		{
			$options = $prefix;
			$enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
			$checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
			$prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		}

		$states = array(1 => array('unpublish', 'JPUBLISHED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JPUBLISHED', false, 'plus-sign', 'plus-sign'),
			0 => array('publish', 'JUNPUBLISHED', 'JLIB_HTML_PUBLISH_ITEM', 'JUNPUBLISHED', false, 'minus-sign', 'minus-sign'),
			2 => array('unpublish', 'JARCHIVED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JARCHIVED', false, 'hdd', 'hdd'),
			-2 => array('publish', 'JTRASHED', 'JLIB_HTML_PUBLISH_ITEM', 'JTRASHED', false, 'trash', 'trash'));

		// Special state for dates
		if ($publish_up || $publish_down)
		{
			$nullDate = JFactory::getDbo()->getNullDate();
			$nowDate = JFactory::getDate()->toUnix();

			$tz = new DateTimeZone(JFactory::getUser()->getParam('timezone', JFactory::getConfig()->get('offset')));

			$publish_up = ($publish_up != $nullDate) ? JFactory::getDate($publish_up, 'UTC')->setTimeZone($tz) : false;
			$publish_down = ($publish_down != $nullDate) ? JFactory::getDate($publish_down, 'UTC')->setTimeZone($tz) : false;

			// Create tip text, only we have publish up or down settings
			$tips = array();

			if ($publish_up)
			{
				$tips[] = JText::sprintf('JLIB_HTML_PUBLISHED_START', $publish_up->format(JDate::$format, true));
			}

			if ($publish_down)
			{
				$tips[] = JText::sprintf('JLIB_HTML_PUBLISHED_FINISHED', $publish_down->format(JDate::$format, true));
			}

			$tip = empty($tips) ? false : implode('<br/>', $tips);

			// Add tips and special titles
			foreach ($states as $key => $state)
			{
				// Create special titles for published items
				if ($key == 1)
				{
					$states[$key][2] = $states[$key][3] = 'JLIB_HTML_PUBLISHED_ITEM';

					if ($publish_up > $nullDate && $nowDate < $publish_up->toUnix())
					{
						$states[$key][2] = $states[$key][3] = 'JLIB_HTML_PUBLISHED_PENDING_ITEM';
						$states[$key][5] = $states[$key][6] = 'pending';
					}

					if ($publish_down > $nullDate && $nowDate > $publish_down->toUnix())
					{
						$states[$key][2] = $states[$key][3] = 'JLIB_HTML_PUBLISHED_EXPIRED_ITEM';
						$states[$key][5] = $states[$key][6] = 'expired';
					}
				}

				// Add tips to titles
				if ($tip)
				{
					$states[$key][1] = JText::_($states[$key][1]);
					$states[$key][2] = JText::_($states[$key][2]) . '::' . $tip;
					$states[$key][3] = JText::_($states[$key][3]) . '::' . $tip;
					$states[$key][4] = true;
				}
			}

			return self::state($states, $value, $i, array('prefix' => $prefix, 'translate' => !$tip), $enabled, true, $checkbox);
		}

		return self::state($states, $value, $i, $prefix, $enabled, true, $checkbox);
	}

	/**
	 * Returns a isDefault state on a grid
	 *
	 * @param   integer       $value     The state value.
	 * @param   integer       $i         The row index
	 * @param   string|array  $prefix    An optional task prefix or an array of options
	 * @param   boolean       $enabled   An optional setting for access control on the action.
	 * @param   string        $checkbox  An optional prefix for checkboxes.
	 *
	 * @return  string  The HTML code
	 */
	public static function isdefault($value, $i, $prefix = '', $enabled = true, $checkbox = 'cb')
	{
		if (is_array($prefix))
		{
			$options  = $prefix;
			$enabled  = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
			$checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
			$prefix   = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		}

		$states = array(
			1 => array('unsetDefault', 'JDEFAULT', 'JLIB_HTML_UNSETDEFAULT_ITEM', 'JDEFAULT', false, 'star', 'star'),
			0 => array('setDefault', '', 'JLIB_HTML_SETDEFAULT_ITEM', '', false, 'star-empty', 'star-empty'),
		);

		return self::state($states, $value, $i, $prefix, $enabled, true, $checkbox);
	}

	/**
	 * Returns a checked-out icon
	 *
	 * @param   integer       $i           The row index.
	 * @param   string        $editorName  The name of the editor.
	 * @param   string        $time        The time that the object was checked out.
	 * @param   string|array  $prefix      An optional task prefix or an array of options
	 * @param   boolean       $enabled     True to enable the action.
	 * @param   string        $checkbox    An optional prefix for checkboxes.
	 *
	 * @return  string  The required HTML.
	 */
	public static function checkedout($i, $editorName, $time, $prefix = '', $enabled = false, $checkbox = 'cb')
	{
		if (is_array($prefix))
		{
			$options = $prefix;
			$enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
			$checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
			$prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		}

		$text = addslashes(htmlspecialchars($editorName, ENT_COMPAT, 'UTF-8'));
		$date = addslashes(htmlspecialchars(JHtml::_('date', $time, JText::_('DATE_FORMAT_LC')), ENT_COMPAT, 'UTF-8'));
		$time = addslashes(htmlspecialchars(JHtml::_('date', $time, 'H:i'), ENT_COMPAT, 'UTF-8'));
		$active_title = JText::_('JLIB_HTML_CHECKIN') . '::' . $text . '<br />' . $date . '<br />' . $time;
		$inactive_title = JText::_('JLIB_HTML_CHECKED_OUT') . '::' . $text . '<br />' . $date . '<br />' . $time;

		return self::action(
			$i, 'checkin', $prefix, JText::_('JLIB_HTML_CHECKED_OUT'), $active_title, $inactive_title, true, 'checkedout',
			'checkedout', $enabled, false, $checkbox
		);
	}

	/**
	 * Creates a order-up action icon.
	 *
	 * @param   integer       $i         The row index.
	 * @param   string        $task      An optional task to fire.
	 * @param   string|array  $prefix    An optional task prefix or an array of options
	 * @param   string        $text      An optional text to display
	 * @param   boolean       $enabled   An optional setting for access control on the action.
	 * @param   string        $checkbox  An optional prefix for checkboxes.
	 *
	 * @return  string  The required HTML.
	 */
	public static function orderUp($i, $task = 'orderup', $prefix = '', $text = 'JLIB_HTML_MOVE_UP', $enabled = true, $checkbox = 'cb')
	{
		if (is_array($prefix))
		{
			$options  = $prefix;
			$text     = array_key_exists('text', $options) ? $options['text'] : $text;
			$enabled  = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
			$checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
			$prefix   = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		}

		return self::action($i, $task, $prefix, $text, $text, $text, false, 'uparrow', 'uparrow_disabled', $enabled, true, $checkbox);
	}

	/**
	 * Creates a order-down action icon.
	 *
	 * @param   integer       $i         The row index.
	 * @param   string        $task      An optional task to fire.
	 * @param   string|array  $prefix    An optional task prefix or an array of options
	 * @param   string        $text      An optional text to display
	 * @param   boolean       $enabled   An optional setting for access control on the action.
	 * @param   string        $checkbox  An optional prefix for checkboxes.
	 *
	 * @return  string  The required HTML.
	 */
	public static function orderDown($i, $task = 'orderdown', $prefix = '', $text = 'JLIB_HTML_MOVE_DOWN', $enabled = true, $checkbox = 'cb')
	{
		if (is_array($prefix))
		{
			$options  = $prefix;
			$text     = array_key_exists('text', $options) ? $options['text'] : $text;
			$enabled  = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
			$checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
			$prefix   = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		}

		return self::action($i, $task, $prefix, $text, $text, $text, false, 'downarrow', 'downarrow_disabled', $enabled, true, $checkbox);
	}

	/**
	 * Method to sort a column in a grid
	 *
	 * @param   string  $title          The link title
	 * @param   string  $order          The order field for the column
	 * @param   string  $direction      The current direction
	 * @param   mixed   $selected       The selected ordering
	 * @param   string  $task           An optional task override
	 * @param   string  $new_direction  An optional direction for the new column
	 * @param   string  $tip            An optional text shown as tooltip title instead of $title
	 * @param   string  $icon           Icon to show
	 *
	 * @return  string
	 */
	public static function sort($title, $order, $direction = 'asc', $selected = 0, $task = null, $new_direction = 'asc', $tip = '', $icon = null)
	{
		JHtml::_('rbootstrap.tooltip');
		static::main();

		$class   = null;
		$classes = array();
		$classes[] = 'js-order-col hasTip';

		$direction      = strtolower($direction);
		$directionIcons = array('icon-chevron-up', 'icon-chevron-down');
		$index          = (int) ($direction == 'desc');

		if ($order != $selected)
		{
			$direction = $new_direction;
		}
		else
		{
			$classes[] = 'active';
			$direction = ($direction == 'desc') ? 'asc' : 'desc';
		}

		if ($classes)
		{
			$class = ' class="' . implode(' ', $classes) . '"';
		}

		// Attribute data-name is used to autopopulate select order fields
		$dataName = (!is_null($title)) ? JText::_($title) : $order;

		$html = '<a href="#" onclick="return false;"' . $class
			. ' data-order="' . $order . '" data-name="' . $dataName . '" title="'
			. JText::_($tip ? $tip : $title) . '::' . JText::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN')
			. '">';

		if (!is_null($icon))
		{
			$html .= ' <i class="' . $icon . '"></i>';
		}

		// Null title for icons ?
		if (!is_null($title))
		{
			$html .= JText::_($title);
		}

		if ($order == $selected)
		{
			$html .= ' <i class="' . $directionIcons[$index] . '"></i>';
		}

		$html .= '</a>';

		return $html;
	}
}