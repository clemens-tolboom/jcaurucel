<?php

/**
 * @file
 * Contains \Drupal\jcarousel\Plugin\views\style\jcarousel.
 */

namespace Drupal\jcarousel\Plugin\views\style;

use Drupal\jcarousel\jCarouselSkinsManager;
use Drupal\views\Plugin\views\style;
use Drupal\Core\Form\FormStateInterface;

/**
 * Style plugin to render each item in a grid cell.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "jcarousel",
 *   title = @Translation("jCarousel"),
 *   help = @Translation("Displays rows in a jCarousel."),
 *   theme = "jcarousel_view",
 *   display_types = {"normal"}
 * )
 */
class jcarousel extends style\StylePluginBase {

  /**
   * Does the style plugin allows to use style plugins.
   *
   * @var bool
   */
  protected $usesRowPlugin = TRUE;

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['wrap'] = array('default' => NULL);
    $options['skin'] = array('default' => 'default');
    $options['visible'] = array('default' => NULL);
    $options['responsive'] = array('default' => 0);
    $options['scroll'] = array('default' => '');
    $options['auto'] = array('default' => 0);
    $options['autoPause'] = array('default' => '1');
    $options['animation'] = array('default' => '');
    $options['start'] = array('default' => '1');
    $options['easing'] = array('default' => NULL);
    $options['vertical'] = array('default' => FALSE);
    $options['navigation'] = array('default' => '');
    $options['ajax'] = array('default' => 0);
    return $options;
  }

  /**
   * Wraps the skins manager.
   *
   * @return \Drupal\jcarousel\jCarouselSkinsManager
   */
  protected function skinsManager() {
    return \Drupal::service('jcarousel.skins.manager');
  }

  /**
   * Returns keyed array of jCarousel skins.
   * 
   * @return array
   *   Keys array of skins
   */
  public function getSkins(){
    return $this->skinsManager()->getDefinitions();
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    // Build the list of skins as options.
    $skins = $this->getSkins();
    foreach ($skins as $key => $skin) {
      $skins[$key] = $skin['label'];
    }
    $skins[''] = $this->t('None');

    // Number of options to provide in count-based options.
    $start_range = range(-10, 10);
    $range = array_combine($start_range, $start_range);
    // Remove '0'.
    unset($range[0]);
    $auto_range = array('' => t('Auto')) + array_combine(range(1, 10), range(1, 10));

    $form['description'] = array(
      '#type' => 'markup',
      '#value' => '<div class="messages">' . t('The jCarousel style is affected by several other settings within the display. Enable the "Use AJAX" option on your display to have items loaded dynamically. The "Items to display" option will determine how many items are preloaded into the carousel on each AJAX request. Non-AJAX carousels will contain the total number of items set in the "Items to display" option. Carousels may not be used with the "Use pager" option.') . '</div>',
    );

    $form['wrap'] = array(
      '#type' => 'select',
      '#title' => t('Wrap content'),
      '#default_value' => $this->options['wrap'],
      '#description' => t('Specifies whether to wrap at the first/last item (or both) and jump back to the start/end.'),
      '#options' => array(
        0 => t('Disabled'),
        'circular' => t('Circular'),
        'both' => t('Both'),
        'last' => t('Last'),
        'first' => t('First'),
      ),
    );
    $form['skin'] = array(
      '#type' => 'select',
      '#title' => t('Skin'),
      '#default_value' => $this->options['skin'],
      '#options' => $skins,
      '#description' => t('Skins may be provided by other modules. Set to "None" if your theme includes carousel theming directly in style.css or another stylesheet. "None" does not include any built-in navigation, arrows, or positioning at all.'),
    );
    $form['responsive'] = array(
      '#type' => 'checkbox',
      '#title' => t('Responsive (number of items)'),
      '#default_value' => $this->options['responsive'],
      '#description' => t('Select this option to have the carousel automatically adjust the number of visible items and the number of items to scroll at a time based on the available width.') . ' <strong>' . t('Changing this option will override the "Visible" and "Scroll" options and set carousel orientation to "horizontal".') . '</strong>',
    );
    $form['visible'] = array(
      '#type' => 'select',
      '#title' => t('Number of visible items'),
      '#options' => $auto_range,
      '#default_value' => $this->options['visible'],
      '#description' => t('Set an exact number of items to show at a time. It is recommended to leave set this to "auto", in which the number of items will be determined automatically by the space available to the carousel.') . ' <strong>' . t('Changing this option will override "width" properties set in your CSS.') . '</strong>',
    );
    $form['scroll'] = array(
      '#type' => 'select',
      '#title' => t('Scroll'),
      '#description' => t('The number of items to scroll at a time. The "auto" setting scrolls all the visible items.'),
      '#options' => $auto_range,
      '#default_value' => $this->options['scroll'],
    );
    $form['auto'] = array(
      '#type' => 'textfield',
      '#title' => t('Auto-scroll after'),
      '#size' => 4,
      '#maxlength' => 4,
      '#default_value' => $this->options['auto'],
      '#field_suffix' => ' ' . t('seconds'),
      '#description' => t('Specifies how many seconds to periodically auto-scroll the content. If set to 0 (default) then autoscrolling is turned off.'),
    );
    $form['navigation'] = array(
      '#type' => 'select',
      '#title' => t('Enable navigation'),
      '#options' => array(
        '' => t('None'),
        'before' => t('Before'),
        'after' => t('After'),
      ),
      '#default_value' => $this->options['navigation'],
      '#description' => t('Enable a clickable navigation list to jump straight to a given page.'),
    );
    $form['ajax'] = array(
      '#type' => 'checkbox',
      '#title' => t('AJAX load pages'),
      '#default_value' => $this->options['ajax'],
      '#description' => t('The number of items set in the pager settings will be preloaded. All additional pages will be loaded by AJAX as needed.'),
    );

    $form['advanced'] = array(
      '#type' => 'fieldset',
      '#title' => t('Advanced'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#parents' => array('style_options'),
    );
    $form['advanced']['animation'] = array(
      '#type' => 'textfield',
      '#title' => t('Animation speed'),
      '#size' => 10,
      '#maxlength' => 10,
      '#default_value' => $this->options['animation'],
      '#description' => t('The speed of the scroll animation as string in jQuery terms ("slow"  or "fast") or milliseconds as integer (See <a href="http://api.jquery.com/animate/">jQuery Documentation</a>).'),
    );
    $form['advanced']['easing'] = array(
      '#type' => 'textfield',
      '#title' => t('Easing effect'),
      '#size' => 10,
      '#maxlength' => 128,
      '#default_value' => $this->options['easing'],
      '#description' => t('The name of the easing effect that you want to use such as "swing" (the default) or "linear". See list of options in the <a href="http://api.jquery.com/animate/">jQuery Documentation</a>.'),
    );
    $form['advanced']['start'] = array(
      '#type' => 'select',
      '#title' => t('Start position'),
      '#description' => t('The item that will be shown as the first item in the list upon loading. Useful for starting a list in the middle of a set. A negative value allows choosing an item in the end, e.g. -1 is the last item.'),
      '#options' => $range,
      '#default_value' => $this->options['start'],
    );
    $form['advanced']['autoPause'] = array(
      '#type' => 'checkbox',
      '#title' => t('Pause auto-scroll on hover'),
      '#description' => t('If auto-scrolling, pause the carousel when the user hovers the mouse over an item.'),
      '#default_value' => $this->options['autoPause'],
    );
    $form['advanced']['vertical'] = array(
      '#type' => 'checkbox',
      '#title' => t('Vertical'),
      '#description' => t('Specifies wether the carousel appears in horizontal or vertical orientation. Changes the carousel from a left/right style to a up/down style carousel. Defaults to horizontal.'),
      '#default_value' => $this->options['vertical'],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validateOptionsForm(&$form, FormStateInterface $form_state) {
    $errors = parent::validateOptionsForm($form, $form_state);
    if ($this->view->usePager()) {
      $errors[] = t('The jCarousel style cannot be used with a pager. Disable the "Use pager" option for this display.');
    }
    return $errors;
  }

}