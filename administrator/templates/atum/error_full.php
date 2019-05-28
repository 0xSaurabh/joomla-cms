<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use OzdemirBurak\Iris\Color\Hex;

/** @var JDocumentError $this */

$app   = Factory::getApplication();
$lang  = $app->getLanguage();
$input = $app->input;
$wa    = $this->getWebAssetManager();

// Detecting Active Variables
$option     = $input->get('option', '');
$view       = $input->get('view', '');
$layout     = $input->get('layout', 'default');
$task       = $input->get('task', 'display');
$itemid     = $input->get('Itemid', '');
$cpanel     = $option === 'com_cpanel';
$hiddenMenu = $app->input->get('hidemainmenu');
$joomlaLogo = $this->baseurl . '/templates/' . $this->template . '/images/logo.svg';

// Add JavaScript
HTMLHelper::_('script', 'vendor/focus-visible/focus-visible.min.js', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('script', 'vendor/css-vars-ponyfill/css-vars-ponyfill.min.js', ['version' => 'auto', 'relative' => true]);

// Logos (params are not available)
$siteLogo  = $this->baseurl . '/templates/' . $this->template . '/images/logo-joomla-blue.svg';
$smallLogo = $this->baseurl . '/templates/' . $this->template . '/images/logo-blue.svg';

// Load template CSS file
HTMLHelper::_('stylesheet', 'fontawesome.css', ['version' => 'auto', 'relative' => true]);

// Enable assets
$wa->enableAsset('template.atum.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr'));

// Load specific language related CSS
HTMLHelper::_('stylesheet', 'administrator/language/' . $lang->getTag() . '/' . $lang->getTag() . '.css', ['version' => 'auto']);

// Load specific template related JS
HTMLHelper::_('script', 'media/templates/' . $this->template . '/js/template.min.js', ['version' => 'auto']);

// Set some meta data
$this->setMetaData('viewport', 'width=device-width, initial-scale=1');
$this->setMetaData('theme-color', '#1c3d5c');
$this->addScriptDeclaration('cssVars();');

// Opacity must be set before displaying the DOM, so don't move to a CSS file
$css = '
	.container-main > * {
		opacity: 0;
	}
	.sidebar-wrapper > * {
		opacity: 0;
	}
';

$this->addStyleDeclaration($css);

HTMLHelper::getServiceRegistry()->register('atum', 'Joomla\\Template\\Atum\\Administrator\\Service\\HTML\\Atum');

HTMLHelper::_('atum.rootcolors', $this->params);
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="metas"/>
	<jdoc:include type="styles"/>
	<jdoc:include type="scripts"/>
</head>

<body class="admin <?php echo $option . ' view-' . $view . ' layout-' . $layout . ($task ? ' task-' . $task : ''); ?>">

<noscript>
	<div class="alert alert-danger" role="alert">
		<?php echo Text::_('JGLOBAL_WARNJAVASCRIPT'); ?>
	</div>
</noscript>

<?php // Header ?>
<header id="header" class="header">
	<div class="d-flex">
		<div class="header-title d-flex mr-auto">
			<div class="d-flex">
				<?php // No home link in edit mode (so users can not jump out) and control panel (for a11y reasons) ?>
				<div class="logo">
					<img src="<?php echo $siteLogo; ?>" alt="<?php echo $logoAlt; ?>">
					<img class="logo-small" src="<?php echo $smallLogo; ?>" alt="<?php echo $logoSmallAlt; ?>">
				</div>
			</div>
			<jdoc:include type="modules" name="title"/>
		</div>
		<div class="header-items d-flex ml-auto">
			<jdoc:include type="modules" name="status" style="header-element"/>
		</div>
	</div>
</header>

<?php // Wrapper ?>
<div id="wrapper" class="d-flex wrapper<?php echo $hiddenMenu ? '0' : ''; ?>">

	<?php // Sidebar ?>
	<?php if (!$hiddenMenu) : ?>
		<div id="sidebar-wrapper" class="sidebar-wrapper" <?php echo $hiddenMenu ? 'data-hidden="' . $hiddenMenu . '"' : ''; ?>>
			<jdoc:include type="modules" name="menu" style="none"/>			
		</div>
	<?php endif; ?>

	<?php // container-fluid ?>
	<div class="container-fluid container-main">
		<?php if (!$cpanel) : ?>
			<?php // Subheader ?>
			<a class="btn btn-subhead d-md-none d-lg-none d-xl-none" data-toggle="collapse"
			   data-target=".subhead-collapse"><?php echo Text::_('TPL_ATUM_TOOLBAR'); ?>
				<span class="icon-wrench"></span></a>
			<div id="subhead" class="subhead">
				<div id="container-collapse" class="container-collapse"></div>
				<div class="row">
					<div class="col-md-12">
						<jdoc:include type="modules" name="toolbar" style="no"/>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<section id="content" class="content">
			<?php // Begin Content ?>
			<jdoc:include type="modules" name="top" style="xhtml"/>
			<div class="row">
				<div class="col-md-12">
					<h1><?php echo Text::_('JERROR_AN_ERROR_HAS_OCCURRED'); ?></h1>
					<blockquote class="blockquote">
						<span class="badge badge-secondary"><?php echo $this->error->getCode(); ?></span>
						<?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?>
					</blockquote>
					<?php if ($this->debug) : ?>
						<div>
							<?php echo $this->renderBacktrace(); ?>
							<?php // Check if there are more Exceptions and render their data as well ?>
							<?php if ($this->error->getPrevious()) : ?>
								<?php $loop = true; ?>
								<?php // Reference $this->_error here and in the loop as setError() assigns errors to this property and we need this for the backtrace to work correctly ?>
								<?php // Make the first assignment to setError() outside the loop so the loop does not skip Exceptions ?>
								<?php $this->setError($this->_error->getPrevious()); ?>
								<?php while ($loop === true) : ?>
									<p><strong><?php echo Text::_('JERROR_LAYOUT_PREVIOUS_ERROR'); ?></strong></p>
									<p><?php echo htmlspecialchars($this->_error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></p>
									<?php echo $this->renderBacktrace(); ?>
									<?php $loop = $this->setError($this->_error->getPrevious()); ?>
								<?php endwhile; ?>
								<?php // Reset the main error object to the base error ?>
								<?php $this->setError($this->error); ?>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<p>
						<a href="<?php echo $this->baseurl; ?>" class="btn btn-secondary">
							<span class="fa fa-dashboard" aria-hidden="true"></span>
							<?php echo Text::_('JGLOBAL_TPL_CPANEL_LINK_TEXT'); ?></a>
					</p>
				</div>

				<?php if ($this->countModules('bottom')) : ?>
					<jdoc:include type="modules" name="bottom" style="xhtml"/>
				<?php endif; ?>
			</div>
			<?php // End Content ?>
		</section>

		<div class="notify-alerts">
			<jdoc:include type="message"/>
		</div>
	</div>
</div>
<jdoc:include type="modules" name="debug" style="none"/>
</body>
</html>
