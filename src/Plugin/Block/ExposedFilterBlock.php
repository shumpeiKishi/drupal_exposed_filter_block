<?php

namespace Drupal\exposed_filter_block_for_block_v\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'ExposedFilterBlock' block.
 *
 * @Block(
 *  id = "exposed_filter_block",
 *  admin_label = @Translation("Exposed filter block"),
 * )
 */

class ExposedFilterBlock extends BlockBase implements BlockPluginInterface {

    /**
     * {@inheritdoc}
     */
    public function build() {

      $build = [];
      $config = $this->getConfiguration();

      // Get config values.
      if (!empty($config['view_id']) && !empty($config['display_id']) && !empty($config['redirect_path']) ) {
        $view_id = $config['view_id'];
        $display_id = $config['display_id'];
        $redirect_path = $config['redirect_path'];

        $build['default_block']['#markup'] = $this->getExposedWidgets($view_id, $display_id, $redirect_path);

      } else {
        $build['default_block']['#markup'] = 'No exposed filter for this view';
      }

      return $build;

    }

    /**
     * {@inheritdoc}
     */
    public function blockForm($form, FormStateInterface $form_state) {

      $views = \Drupal\views\Views::getViewsAsOptions();

      $form = parent::blockForm($form, $form_state);

      $config = $this->getConfiguration();

      $form['default_block_view_id'] = array (
        '#type' => 'textfield',
        '#title' => $this->t('View Id'),
        '#description' => $this->t('View id of the exposed filter'),
        '#default_value' => isset($config['view_id']) ? $config['view_id'] : '',
      );

      $form['default_block_view_display_id'] = array (
        '#type' => 'textfield',
        '#title' => $this->t('View Display Id'),
        '#description' => $this->t('View Display ID of the view'),
        '#default_value' => isset($config['display_id']) ? $config['display_id'] : '',
      );

      $form['default_block_redirect_path'] = array (
        '#type' => 'textfield',
        '#title' => $this->t('Redirect Path'),
        '#description' => $this->t('Redirect path in case the block is placed without the parent view'),
        '#default_value' => isset($config['redirect_path']) ? $config['redirect_path'] : '',
      );

      return $form;
    }

    /**
   * {@inheritdoc}
   */
    public function blockSubmit($form, FormStateInterface $form_state) {
      $this->setConfigurationValue('view_id', $form_state->getValue('default_block_view_id'));
      $this->setConfigurationValue('display_id', $form_state->getValue('default_block_view_display_id'));
      $this->setConfigurationValue('redirect_path', $form_state->getValue('default_block_redirect_path'));
    }

    public function getExposedWidgets($view_id, $view_display_id, $action_path = '/') {
      $renderer = \Drupal::service('renderer');

      // Get the view machine id.
      $view = \Drupal\views\Views::getView($view_id);
      // Set the display machine id.
      $view->setDisplay($view_display_id);

      // Load view.
      $view->build();

      // Get exposed widgets.
      $exposed_widgets = $view->exposed_widgets;

      // Alter exposed filter form's action attribute.
      $exposed_widgets['#action'] = $action_path;

      // Alter duplicated Form and element IDs.
      $exposed_widgets['#id'] = $exposed_widgets['#id'] . '-exposed-block';

      // Alter field ids.
      foreach ($exposed_widgets['#info'] as $field) {
        $field_id = $field['value'];
        $exposed_widgets[$field_id]['#id'] = $field_id . '-exposed-block';
      }

      // Alter submit button id.
      $exposed_widgets['actions']['submit']['#id'] = $exposed_widgets['actions']['submit']['#id'] . '-exposed-block';

      return $renderer->render($exposed_widgets);
    }
  }
