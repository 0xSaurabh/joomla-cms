<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/* @var $menu JAdminCSSMenu */

$shownew = (boolean) $params->get('shownew', 1);
$showhelp = $params->get('showhelp', 1);
$user = JFactory::getUser();
$lang = JFactory::getLanguage();

/*
 * Site Submenu
 */
$menu->addChild(new JMenuNode(JText::_('MOD_MENU_SYSTEM'), '#', 'class:cog fa-fw'), true);
$menu->addChild(new JMenuNode(JText::_('MOD_MENU_CONTROL_PANEL'), 'index.php'));

if ($user->authorise('core.admin'))
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_CONFIGURATION'), 'index.php?option=com_config'));
}

if ($user->authorise('core.manage', 'com_checkin'))
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_GLOBAL_CHECKIN'), 'index.php?option=com_checkin'));
}

if ($user->authorise('core.manage', 'com_cache'))
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_CLEAR_CACHE'), 'index.php?option=com_cache'));
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_PURGE_EXPIRED_CACHE'), 'index.php?option=com_cache&view=purge'));
}

if ($user->authorise('core.admin'))
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_SYSTEM_INFORMATION'), 'index.php?option=com_admin&view=sysinfo'));
}

$menu->getParent();

/*
 * Users Submenu
 */
if ($user->authorise('core.manage', 'com_users'))
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_USERS'), '#', 'class:users fa-fw'), true);
	$createUser = $shownew && $user->authorise('core.create', 'com_users');
	$createGrp  = $user->authorise('core.admin', 'com_users');

	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_USER_MANAGER'), 'index.php?option=com_users&view=users'), $createUser);

	if ($createUser)
	{
		$menu->getParent();
	}

	if ($createGrp)
	{
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_GROUPS'), 'index.php?option=com_users&view=groups'), $createUser);

		if ($createUser)
		{
			$menu->getParent();
		}

		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_LEVELS'), 'index.php?option=com_users&view=levels'), $createUser);

		if ($createUser)
		{
			$menu->getParent();
		}
	}

	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_NOTES'), 'index.php?option=com_users&view=notes'), $createUser);

	if ($createUser)
	{
		$menu->getParent();
	}

	$menu->addChild(
		new JMenuNode(
			JText::_('MOD_MENU_COM_USERS_NOTE_CATEGORIES'), 'index.php?option=com_categories&view=categories&extension=com_users'),
		$createUser
	);

	if ($createUser)
	{
		$menu->getParent();
	}

	if (JFactory::getApplication()->get('massmailoff') != 1)
	{
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_MASS_MAIL_USERS'), 'index.php?option=com_users&view=mail'));
	}

	$menu->getParent();
}

/*
 * Menus Submenu
 */
if ($user->authorise('core.manage', 'com_menus'))
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_MENUS'), '#', 'class:list fa-fw'), true);
	$createMenu = $shownew && $user->authorise('core.create', 'com_menus');

	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_MENU_MANAGER'), 'index.php?option=com_menus&view=menus'), $createMenu);

	if ($createMenu)
	{
		$menu->getParent();
	}

	// Menu Types
	$menuTypes = ModMenuHelper::getMenus();
	$menuTypes = JArrayHelper::sortObjects($menuTypes, 'title', 1, false);

	foreach ($menuTypes as $menuType)
	{
		if (!$user->authorise('core.manage', 'com_menus.menu.' . (int) $menuType->id))
		{
			continue;
		}

		$alt = '*' . $menuType->sef . '*';

		if ($menuType->home == 0)
		{
			$titleicon = '';
		}
		elseif ($menuType->home == 1 && $menuType->language == '*')
		{
			$titleicon = ' <span class="fa fa-home"></span>';
		}
		elseif ($menuType->home > 1)
		{
			$titleicon = ' <span>'
				. JHtml::_('image', 'mod_languages/icon-16-language.png', $menuType->home, array('title' => JText::_('MOD_MENU_HOME_MULTIPLE')), true)
				. '</span>';
		}
		else
		{
			$image = JHtml::_('image', 'mod_languages/' . $menuType->image . '.gif', null, null, true, true);

			if (!$image)
			{
				$image = JHtml::_('image', 'mod_languages/icon-16-language.png', $alt, array('title' => $menuType->title_native), true);
			}
			else
			{
				$image = JHtml::_('image', 'mod_languages/' . $menuType->image . '.gif', $alt, array('title' => $menuType->title_native), true);
			}

			$titleicon = ' <span>' . $image . '</span>';
		}

		$menu->addChild(
			new JMenuNode(
				$menuType->title, 'index.php?option=com_menus&view=items&menutype=' . $menuType->menutype, null, null, null, $titleicon
			),
			$user->authorise('core.create', 'com_menus.menu.' . (int) $menuType->id)
		);

		if ($user->authorise('core.create', 'com_menus.menu.' . (int) $menuType->id))
		{
			$menu->addChild(
				new JMenuNode(
					JText::_('MOD_MENU_MENU_MANAGER_NEW_MENU_ITEM'), 'index.php?option=com_menus&view=item&layout=edit&menutype=' . $menuType->menutype,
					null)
			);

			$menu->getParent();
		}
	}

	$menu->getParent();
}

/*
 * Content Submenu
 */
if ($user->authorise('core.manage', 'com_content'))
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COM_CONTENT'), '#', 'class:file-text-o fa-fw'), true);
	$createContent = $shownew && $user->authorise('core.create', 'com_content');
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_ARTICLE_MANAGER'), 'index.php?option=com_content'), $createContent);

	if ($createContent)
	{
		$menu->getParent();
	}

	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_CATEGORY_MANAGER'), 'index.php?option=com_categories&extension=com_content'), $createContent
	);

	if ($createContent)
	{
		$menu->getParent();
	}

	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_FEATURED'), 'index.php?option=com_content&view=featured'));

	if ($user->authorise('core.manage', 'com_media'))
	{
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_MEDIA_MANAGER'), 'index.php?option=com_media'));
	}

	$menu->getParent();
}

/*
 * Components Submenu
 */

// Get the authorised components and sub-menus.
$components = ModMenuHelper::getComponents(true);

// Check if there are any components, otherwise, don't render the menu
if ($components)
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COMPONENTS'), '#', 'class:cube fa-fw'), true);

	foreach ($components as &$component)
	{
		if (!empty($component->submenu))
		{
			// This component has a db driven submenu.
			$menu->addChild(new JMenuNode($component->text, $component->link), true);

			foreach ($component->submenu as $sub)
			{
				$menu->addChild(new JMenuNode($sub->text, $sub->link));
			}

			$menu->getParent();
		}
		else
		{
			$menu->addChild(new JMenuNode($component->text, $component->link));
		}
	}

	$menu->getParent();
}

/*
 * Extensions Submenu
 */
$im = $user->authorise('core.manage', 'com_installer');
$mm = $user->authorise('core.manage', 'com_modules');
$pm = $user->authorise('core.manage', 'com_plugins');
$tm = $user->authorise('core.manage', 'com_templates');
$lm = $user->authorise('core.manage', 'com_languages');

if ($im || $mm || $pm || $tm || $lm)
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_EXTENSIONS'), '#', 'class:cubes fa-fw'), true);

	if ($im)
	{
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_EXTENSION_MANAGER'), '#'), $im);

		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_INSTALL'), 'index.php?option=com_installer'));
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_UPDATE'), 'index.php?option=com_installer&view=update'));
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_MANAGE'), 'index.php?option=com_installer&view=manage'));
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_DISCOVER'), 'index.php?option=com_installer&view=discover'));
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_DATABASE'), 'index.php?option=com_installer&view=database'));
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_WARNINGS'), 'index.php?option=com_installer&view=warnings'));
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_LANGUAGES'), 'index.php?option=com_installer&view=languages'));
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_UPDATESITES'), 'index.php?option=com_installer&view=updatesites'));
		$menu->getParent();
	}

	if ($mm)
	{
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_MODULE_MANAGER'), 'index.php?option=com_modules'));
	}

	if ($pm)
	{
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_PLUGIN_MANAGER'), 'index.php?option=com_plugins'));
	}

	if ($tm)
	{
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_TEMPLATE_MANAGER'), 'index.php?option=com_templates'), $tm);

		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COM_TEMPLATES_SUBMENU_STYLES'), 'index.php?option=com_templates&view=styles'));
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COM_TEMPLATES_SUBMENU_TEMPLATES'), 'index.php?option=com_templates&view=templates'));
		$menu->getParent();
	}

	if ($lm)
	{
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_LANGUAGE_MANAGER'), '#'), $lm);
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COM_LANGUAGES_SUBMENU_INSTALLED'), 'index.php?option=com_languages&view=installed'));
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COM_LANGUAGES_SUBMENU_CONTENT'), 'index.php?option=com_languages&view=languages'));
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COM_LANGUAGES_SUBMENU_OVERRIDES'), 'index.php?option=com_languages&view=overrides'));
		$menu->getParent();
	}

	$menu->getParent();
}

/*
 * Help Submenu
 */
if ($showhelp == 1)
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_HELP'), '#', 'class:info-circle fa-fw'), true);
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_JOOMLA'), 'index.php?option=com_admin&view=help'));
	//$menu->addSeparator();

	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_FORUM'), 'http://forum.joomla.org', null, false, '_blank'));

	if ($forum_url = $params->get('forum_url'))
	{
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_SUPPORT_CUSTOM_FORUM'), $forum_url, null, false, '_blank'));
	}

	$debug = $lang->setDebug(false);

	if ($lang->hasKey('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM_VALUE') && JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM_VALUE') != '')
	{
		$forum_url = 'http://forum.joomla.org/viewforum.php?f=' . (int) JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM_VALUE');
		$lang->setDebug($debug);
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM'), $forum_url, null, false, '_blank'));
	}

	$lang->setDebug($debug);
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_DOCUMENTATION'), 'https://docs.joomla.org', null, false, '_blank'));
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_EXTENSIONS'), 'http://extensions.joomla.org', null, false, '_blank'));
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_TRANSLATIONS'), 'https://community.joomla.org/translations.html', null, false, '_blank'));
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_RESOURCES'), 'http://resources.joomla.org', null, false, '_blank'));
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_COMMUNITY'), 'https://community.joomla.org', null, false, '_blank'));
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_SECURITY'), 'https://developer.joomla.org/security-centre.html', null, false, '_blank'));
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_DEVELOPER'), 'https://developer.joomla.org', null, false, '_blank'));
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_XCHANGE'), 'https://joomla.stackexchange.com', null, false, '_blank'));
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_SHOP'), 'https://community.joomla.org/the-joomla-shop.html', null, false, '_blank'));
	$menu->getParent();
}

/*
 * User Submenu
 */
$menu->addChild(new JMenuNode($user->username, '#', 'class:user fa-fw'), true);
$menu->addChild(new JMenuNode(JText::_('TPL_ATUM_LOGOUT'), JRoute::_('index.php?option=com_login&task=logout&' . JSession::getFormToken() . '=1')));
$menu->getParent();
