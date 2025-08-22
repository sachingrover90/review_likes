<?php

namespace Drupal\role_based_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\user\UserInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Provides a business registration form.
 */
class BusinessRegistrationForm extends FormBase {
  protected $user;
  protected $role;
  protected $routeMatch;

  /**
   * Constructs a new BusinessRegistrationForm.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match service.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'role_based_registration_business_form';
  }
public function access(AccountInterface $account, RouteMatchInterface $route_match, UserInterface $user = NULL) {
  // If user parameter is not provided as entity, try to get it from route
  if (!$user || !$user instanceof UserInterface) {
    // Get the user parameter from route (could be ID or entity)
    $user_param = $route_match->getParameter('user');
    
    // If it's a numeric ID, load the user entity
    if (is_numeric($user_param)) {
      $user = \Drupal::entityTypeManager()->getStorage('user')->load($user_param);
    }
    // If it's already a user entity, use it
    elseif ($user_param instanceof UserInterface) {
      $user = $user_param;
    }
  }
  
  // If still no valid user, deny access
  if (!$user || !$user instanceof UserInterface) {
    return AccessResult::forbidden('No valid user provided.')->addCacheContexts(['url']);
  }

  // Allow access if:
  // 1. The user is accessing their own form, OR
  // 2. The user has administrator privileges
  $is_own_account = $account->id() == $user->id();
  $has_admin_permission = $account->hasPermission('administer users');
  
  if ($is_own_account || $has_admin_permission) {
    // Also check if user has merchant or administrator role
    $user_roles = $user->getRoles();
    if (in_array('merchant', $user_roles) || in_array('administrator', $user_roles)) {
      return AccessResult::allowed()->addCacheableDependency($user);
    }
  }

  return AccessResult::forbidden()->addCacheableDependency($user);
}
  /**
   * {@inheritdoc}
   */
public function buildForm(array $form, FormStateInterface $form_state) {
  $user_param = $this->routeMatch->getParameter('user');
  
  // Handle both cases: numeric ID or User entity
  if (is_numeric($user_param)) {
    $user = \Drupal::entityTypeManager()->getStorage('user')->load($user_param);
  } elseif ($user_param instanceof UserInterface) {
    $user = $user_param;
  } else {
    $user = NULL;
  }
  
  if (!$user instanceof UserInterface) {
    $form['error'] = [
      '#markup' => $this->t('User not found.'),
    ];
    return $form;
  }
  
  $this->user = $user;
  
  // Load existing merchant profile
  $existing_profile = $this->getExistingMerchantProfile($user);
  
  // Check if user has the merchant role
  if (!$this->userHasMerchantRole($user)) {
    $form['access_denied'] = [
      '#markup' => '<div class="messages messages--error">' . 
                   $this->t('You must have a merchant account to access this form.') . 
                   '</div>',
    ];
    return $form;
  }
  
  // Check if user already has a merchant profile
//   if ($existing_profile) {
//     $form['existing_profile'] = [
//       '#markup' => '<div class="messages messages--warning">' . 
//                    $this->t('You already have a merchant profile. <a href="@link">View your profile</a>.', 
//                    ['@link' => $existing_profile->toUrl()->toString()]) . 
//                    '</div>',
//     ];
//   }
  $form['legal_business_name'] = [
    '#type' => 'fieldset',
    '#title' => $this->t('Legal Business Name'),
    '#prefix' => '<div id="brand-names-wrapper">',
    '#suffix' => '</div>',
  ];
  // Legal Business Name field.
  $form['legal_business_name'] = [
    '#type' => 'textfield',
    '#title' => $this->t('Legal Business Name *'),
    '#required' => TRUE,
    '#default_value' => $existing_profile ? $existing_profile->get('field_legal_business_name')->value : '',
    '#attributes' => ['placeholder' => $this->t('Legal Business Name')],
  ];

  // Container for brand names.
  $form['brand_names'] = [
    '#type' => 'fieldset',
    '#title' => $this->t('Brand Name(s) *'),
    '#prefix' => '<div id="brand-names-wrapper">',
    '#suffix' => '</div>',
  ];

  // Initialize brand names if not set.
  if (empty($form_state->get('brand_names_count'))) {
    $brand_count = $existing_profile ? count($existing_profile->get('field_brand_name')) : 1;
    $form_state->set('brand_names_count', $brand_count);
  }

  $brand_count = $form_state->get('brand_names_count');

  // Add brand name fields.
  for ($i = 0; $i < $brand_count; $i++) {
    $default_value = '';
    if ($existing_profile && isset($existing_profile->get('field_brand_name')[$i])) {
      $default_value = $existing_profile->get('field_brand_name')[$i]->value;
    }
    
    $form['brand_names']['brand_name_' . $i] = [
      '#type' => 'textfield',
      '#title' => $i == 0 ? $this->t('Brand Name / Doing Business As *') : $this->t('Brand Name / Doing Business As'),
      '#required' => $i == 0,
      '#default_value' => $default_value,
      '#attributes' => ['placeholder' => $this->t('Brand Name / Doing Business As')],
    ];
    
    if ($i > 0) {
      $form['brand_names']['brand_name_' . $i]['#suffix'] = '<button type="submit" name="remove_brand_' . $i . '" value="' . $i . '" class="button button--danger">' . $this->t('Remove') . '</button>';
    }
  }

  // Add brand button.
  $form['brand_names']['add_brand'] = [
    '#type' => 'submit',
    '#value' => $this->t('+ Add Brand'),
    '#submit' => ['::addBrand'],
    '#ajax' => [
      'callback' => '::ajaxCallbackBrands',
      'wrapper' => 'brand-names-wrapper',
    ],
    '#limit_validation_errors' => [],
    '#access' => $brand_count < 3,
  ];

  $form['brand_names']['brand_help'] = [
    '#markup' => '<div class="description">' . $this->t('Add up to 3 brands you operate under.') . '</div>',
  ];

  // Store Link field.
  $form['store_link'] = [
    '#type' => 'url',
    '#title' => $this->t('Store Link (primary marketplace URL) *'),
    '#required' => TRUE,
    '#default_value' => $existing_profile ? $existing_profile->get('field_store_link')->uri : '',
    '#attributes' => ['placeholder' => $this->t('Store Link')],
  ];

  // Container for platform links.
  $form['platform_links'] = [
    '#type' => 'fieldset',
    '#title' => $this->t('Online Selling Platforms (links) *'),
    '#prefix' => '<div id="platform-links-wrapper">',
    '#suffix' => '</div>',
  ];

  // Initialize platform links if not set.
  if (empty($form_state->get('platform_links_count'))) {
    $platform_count = $existing_profile ? count($existing_profile->get('field_platform_links')) : 1;
    $form_state->set('platform_links_count', $platform_count);
  }

  $platform_count = $form_state->get('platform_links_count');

  // Add platform link fields.
  for ($i = 0; $i < $platform_count; $i++) {
    $default_value = '';
    if ($existing_profile && isset($existing_profile->get('field_platform_links')[$i])) {
      $default_value = $existing_profile->get('field_platform_links')[$i]->uri;
    }
    
    $form['platform_links']['platform_link_' . $i] = [
      '#type' => 'url',
      '#title' => $i == 0 ? $this->t('Online Selling Platforms (links) *') : $this->t('Online Selling Platforms (links)'),
      '#required' => $i == 0,
      '#default_value' => $default_value,
      '#attributes' => ['placeholder' => $this->t('Platform Link')],
    ];
    
    if ($i > 0) {
      $form['platform_links']['platform_link_' . $i]['#suffix'] = '<button type="submit" name="remove_platform_' . $i . '" value="' . $i . '" class="button button--danger">' . $this->t('Remove') . '</button>';
    }
  }

  // Add platform button.
  $form['platform_links']['add_platform'] = [
    '#type' => 'submit',
    '#value' => $this->t('+ Add Platform Link'),
    '#submit' => ['::addPlatform'],
    '#ajax' => [
      'callback' => '::ajaxCallbackPlatforms',
      'wrapper' => 'platform-links-wrapper',
    ],
    '#limit_validation_errors' => [],
  ];

  // Submit button.
  $form['actions'] = [
    '#type' => 'actions',
  ];
  
  $form['actions']['submit'] = [
    '#type' => 'submit',
    '#value' => $this->t('Submit Registration'),
  ];

  // Store existing profile ID for updates
  if ($existing_profile) {
    $form['existing_profile_id'] = [
      '#type' => 'hidden',
      '#value' => $existing_profile->id(),
    ];
  }

  return $form;
}

  /**
   * Get the user's primary role.
   */
  protected function getUserPrimaryRole(UserInterface $user) {
    $roles = $user->getRoles();
    // Remove authenticated role
    $roles = array_diff($roles, ['merchant']);
    
    return !empty($roles) ? reset($roles) : 'merchant';
  }

  /**
   * Check if user has merchant role.
   */
  protected function userHasMerchantRole(UserInterface $user) {
    $roles = $user->getRoles();
    return in_array('merchant', $roles) || in_array('administrator', $roles);
  }

  /**
   * Get existing merchant profile for user.
   */
  protected function getExistingMerchantProfile(UserInterface $user) {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'merchant')
      ->condition('uid', $user->id())
      ->accessCheck(FALSE);
    
    $nids = $query->execute();
    
    if (!empty($nids)) {
      $nid = reset($nids);
      return \Drupal\node\Entity\Node::load($nid);
    }
    
    return FALSE;
  }

  /**
   * AJAX callback for brand fields.
   */
  public function ajaxCallbackBrands(array &$form, FormStateInterface $form_state) {
    return $form['brand_names'];
  }

  /**
   * AJAX callback for platform fields.
   */
  public function ajaxCallbackPlatforms(array &$form, FormStateInterface $form_state) {
    return $form['platform_links'];
  }

  /**
   * Add a brand field.
   */
  public function addBrand(array &$form, FormStateInterface $form_state) {
    $count = $form_state->get('brand_names_count');
    if ($count < 3) {
      $form_state->set('brand_names_count', $count + 1);
    }
    $form_state->setRebuild();
  }

  /**
   * Add a platform field.
   */
  public function addPlatform(array &$form, FormStateInterface $form_state) {
    $count = $form_state->get('platform_links_count');
    $form_state->set('platform_links_count', $count + 1);
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Check if any remove button was clicked.
    $triggering_element = $form_state->getTriggeringElement();
    
    if (strpos($triggering_element['#name'], 'remove_brand') === 0) {
      $index = $triggering_element['#value'];
      $form_state->set('brand_names_count', $form_state->get('brand_names_count') - 1);
      $form_state->setRebuild();
      return;
    }
    
    if (strpos($triggering_element['#name'], 'remove_platform') === 0) {
      $index = $triggering_element['#value'];
      $form_state->set('platform_links_count', $form_state->get('platform_links_count') - 1);
      $form_state->setRebuild();
      return;
    }
    
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Check if this is an AJAX operation (add/remove buttons)
    $triggering_element = $form_state->getTriggeringElement();
    if ($triggering_element['#name'] == 'add_brand' || 
        $triggering_element['#name'] == 'add_platform' ||
        strpos($triggering_element['#name'], 'remove_brand') === 0 ||
        strpos($triggering_element['#name'], 'remove_platform') === 0) {
      $form_state->setRebuild();
      return;
    }

    // Collect brand names
    $brand_names = [];
    $brand_count = $form_state->get('brand_names_count');
    for ($i = 0; $i < $brand_count; $i++) {
      $value = $form_state->getValue('brand_name_' . $i);
      if (!empty($value)) {
        $brand_names[] = $value;
      }
    }

    // Collect platform links
    $platform_links = [];
    $platform_count = $form_state->get('platform_links_count');
    for ($i = 0; $i < $platform_count; $i++) {
      $value = $form_state->getValue('platform_link_' . $i);
      if (!empty($value)) {
        $platform_links[] = ['uri' => $value];
      }
    }

    // Check if we're updating an existing profile
    $existing_profile_id = $form_state->getValue('existing_profile_id');
    
    if ($existing_profile_id) {
      // Update existing profile
      $node = Node::load($existing_profile_id);
      $node->set('title', $form_state->getValue('legal_business_name'));
      $node->set('field_legal_business_name', $form_state->getValue('legal_business_name'));
      $node->set('field_brand_name', $brand_names);
      $node->set('field_store_link', $form_state->getValue('store_link'));
      $node->set('field_platform_links', $platform_links);
      
      $message = $this->t('Your business registration has been updated successfully.');
    } else {
      // Create new node with the submitted values
      $node = Node::create([
        'type' => 'merchant',
        'title' => $form_state->getValue('legal_business_name'),
        'field_legal_business_name' => $form_state->getValue('legal_business_name'),
        'field_brand_name' => $brand_names,
        'field_store_link' => ['uri' => $form_state->getValue('store_link')],
        'field_platform_links' => $platform_links,
        'uid' => $this->user->id(), // Set the owner to the current user
        'status' => 1, // Published
      ]);
      
      $message = $this->t('Your business registration has been submitted successfully.');
    }
    
    $node->save();

    // Show success message
    \Drupal::messenger()->addMessage($message);
    
    // Redirect to the profile view
    // $form_state->setRedirect('entity.node.canonical', ['node' => $node->id()]);
    $form_state->setRedirect('<current>');
  }
}