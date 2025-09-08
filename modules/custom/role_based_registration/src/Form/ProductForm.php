<?php

namespace Drupal\role_based_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Drupal\user\UserInterface;

class ProductForm extends FormBase {

  public function getFormId() {
    return 'role_based_registration_product_form';
  }

  /**
   * Access check.
   */
  public static function access(AccountInterface $account) {
    $has_merchant_role = in_array('merchant', $account->getRoles());
    $has_permission = $account->hasPermission('create product content');
    return AccessResult::allowedIf($has_merchant_role && $has_permission)
      ->addCacheContexts(['user.roles', 'user.permissions']);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
//  $current_user = \Drupal::currentUser();
//     $user_entity = User::load($current_user->id());
//     $this->user = $user_entity;
    // $this->role = $this->getUserPrimaryRole($user_entity);




    // Extract Data Section
    $form['extract_section'] = [
      '#type' => 'fieldset',
      '#attributes' => ['class' => ['mb-4']],
    ];

    $form['extract_section']['title'] = [
      '#markup' => '<h3 class="mb-4 text-center">Extract Product Details From Platform</h3>',
    ];

    $form['extract_section']['link_container'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['row', 'justify-content-center', 'align-items-baseline', 'mb-3']],
      '#prefix' => '<div class="row justify-content-center">',
    ];

    $form['extract_section']['link_container']['elink'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Provide the link'),
      '#title_display' => 'invisible',
      '#attributes' => [
        'class' => ['form-control'],
        'placeholder' => 'Provide the link',
      ],
      '#prefix' => '<div class="col-md-6 mb-3">',
      '#suffix' => '</div>',
    ];

    $form['extract_section']['link_container']['extract_button'] = [
      '#type' => 'button',
      '#value' => $this->t('Extract Data'),
      '#attributes' => ['class' => ['btn', 'btn-default']],
      '#prefix' => '<div class="col-md-2">',
      '#suffix' => '</div>',
    ];

    $form['extract_section']['hint'] = [
      '#markup' => '<span class="g-title text-center"><i>Please provide the Amazon, ebay, flipkart links only to extract product data</i></span>',
     '#suffix' => '</div>',
    ];



    // Start platform rows
    if ($form_state->get('platform_items') === NULL) {
      $form_state->set('platform_items', [0]); // start with one row
    }
    $platform_items = $form_state->get('platform_items');

    $form['#tree'] = TRUE;
     $form['platform_items']['title'] = [
      '#markup' => '<h4 class="mb-4 text-center">Product Information</h4>',
    ];


    // === Online Selling Platforms Section ===
$form['platforms_wrapper'] = [
  '#type' => 'container',
  '#attributes' => ['id' => 'platforms-wrapper'],
  '#prefix' => '<div class="row justify-content-center"><div id="platforms-wrapper"> <div class="row justify-content-center"> <div class="col-md-10"><div class="mb-3 adminplatform"> <div class="platform-wrapper">',
  '#suffix' => '</div> </div> </div> </div></div>',
];

foreach ($platform_items as $delta) {
  // Create a row container for each platform
  $form['platforms_wrapper']['platforms'][$delta] = [
    '#type' => 'container',
    '#attributes' => ['class' => ['row', 'mb-2', 'platform-row']],
    '#prefix' => '<div class="row platform-wrapper mt-3">',
    '#suffix' => '</div>',
  ];
    $form['platforms_wrapper']['platforms'][$delta]['title'] = [
      '#markup' => '<h5 class="required mb-3">Online Selling Platforms (links)</h5>',
      '#title_display' => 'after',
    ];

  // Link field
  $form['platforms_wrapper']['platforms'][$delta]['link'] = [
    '#type' => 'textfield',
    // '#title' => $this->t('Online Selling Platforms (links)'),
    '#required' => TRUE,
    '#attributes' => ['placeholder' => 'add more link same product', 'class' => ['form-control me-4']],
    '#default_value' => $form_state->getValue(['platforms_wrapper', 'platforms', $delta, 'link']),
    '#prefix' => '<div class="col-md-5">',
    '#suffix' => '</div>',
  ];

  // Price field
  $form['platforms_wrapper']['platforms'][$delta]['price'] = [
    '#type' => 'textfield',
    '#attributes' => ['placeholder' => 'Price', 'class' => ['form-control me-4']],
    '#required' => TRUE,
    '#default_value' => $form_state->getValue(['platforms_wrapper', 'platforms', $delta, 'price']),
    '#prefix' => '<div class="col-md-3">',
    '#suffix' => '</div>',
  ];

  // Coupon field
  $form['platforms_wrapper']['platforms'][$delta]['coupon'] = [
    '#type' => 'textfield',
    '#attributes' => ['placeholder' => 'Coupon Code', 'class' => ['form-control me-4']],
    '#required' => TRUE,
    '#default_value' => $form_state->getValue(['platforms_wrapper', 'platforms', $delta, 'coupon']),
    '#prefix' => '<div class="col-md-3">',
    '#suffix' => '</div>',
  ];

  // Remove button (only if more than one row)
  if (count($platform_items) > 1) {
    $form['platforms_wrapper']['platforms'][$delta]['remove'] = [
      '#type' => 'submit',
      '#value' => $this->t('Remove'),
      '#submit' => ['::removePlatformSubmit'],
      '#ajax' => [
        'callback' => '::ajaxCallback',
        'wrapper' => 'platforms-wrapper',
      ],
      '#name' => 'remove-' . $delta,
      '#limit_validation_errors' => [],
      '#attributes' => ['class' => ['button--danger', 'remove-platform-btn', 'btn danger remove-btn']],
      '#prefix' => '<div class="col-md-1">',
      '#suffix' => '</div>',
    ];
  } else {
    // Add empty column for alignment when no remove button
    $form['platforms_wrapper']['platforms'][$delta]['empty'] = [
      '#markup' => '<div class="col-md-1"></div>',
    ];
  }
}

// Add button
$form['platforms_wrapper']['add'] = [
  '#type' => 'submit',
  '#value' => $this->t('+ Add Platform Link'),
  '#submit' => ['::addPlatformSubmit'],
  '#ajax' => [
    'callback' => '::ajaxCallback',
    'wrapper' => 'platforms-wrapper',
  ],
  '#limit_validation_errors' => [],
  '#attributes' => ['class' => ['button--primary', 'add-platform-btn', 'btn addPlatformBtn mt-2']],
];

// Hint text
$form['platforms_wrapper']['hint'] = [
  '#markup' => '<div class="row"><div class="col-md-12 text-center"><span class="hint mt-2 mb-3">Add up to 20 Platform Links you operate under.</span></div></div>',
  '#prefix' => '<div class="row"><div class="col-md-12 text-center">',
  '#suffix' => '</div></div>',
];

    // === Product Fields ===

 // Affiliated Link
    $form['affiliated_link'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Affiliated Link *'),
      '#title_display' => 'before',
      '#required' => TRUE,
      '#maxlength' => 255,
      '#attributes' => ['class' => ['form-control', 'mb-3']],
      '#prefix' => '<div class="col-md-10 mb-3">',
      '#suffix' => '</div>',
    ];


    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Product Title'),
       '#title_display' => 'before',
      '#required' => TRUE,
      '#maxlength' => 255,
      '#attributes' => ['class' => ['form-control', 'mb-3']],
      '#prefix' => '<div class="col-md-5 mb-3">',
      '#suffix' => '</div>',
      '#prefix' => '<div class="col-md-5 mb-3">',
    ];

    

    $form['image'] = [      
      '#type' => 'managed_file',
      '#title' => $this->t('Upload Image'),
      '#title_display' => 'before',
      '#upload_location' => 'public://products/',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg gif'],
        'file_validate_size' => [2 * 1024 * 1024],
      ],
      '#required' => TRUE,
      '#suffix' => '</div>',
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Product Description'),
      '#title_display' => 'before',
      '#required' => TRUE,
      '#attributes' => ['class' => ['form-control'], 'rows' => 3],
      '#prefix' => '<div class="col-md-5 mb-3">',
      '#suffix' => '</div>',
    ];

    $form['price'] = [
      '#type' => 'number',
       '#title' => $this->t('Price'),
      '#title_display' => 'before',
      '#step' => 0.01,
      '#min' => 0,
      '#required' => TRUE,
      '#attributes' => ['class' => ['form-control', 'mb-3']],
      '#prefix' => '<div class="col-md-5 mb-3">',
      '#suffix' => '</div>',
    ];


     // Sold By
    $form['sold_by'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sold By'),
      '#title_display' => 'before',
    //   '#target_type' => 'user',
    //   '#default_value' => $user_entity,
      '#required' => TRUE,
    //   '#selection_settings' => ['include_anonymous' => FALSE],
      '#attributes' => ['class' => ['form-control', 'mb-3']],
      '#prefix' => '<div class="col-md-5 mb-3">',
      '#suffix' => '</div>',
    ];


    // Rating
    $form['rating'] = [
      '#type' => 'select',
      '#title' => $this->t('Rating'),
      '#title_display' => 'before',
      '#options' => [
        '1' => $this->t('1 Star'),
        '2' => $this->t('2 Stars'),
        '3' => $this->t('3 Stars'),
        '4' => $this->t('4 Stars'),
        '5' => $this->t('5 Stars'),
      ],
      '#required' => TRUE,
      '#attributes' => ['class' => ['form-control', 'mb-3']],
      '#prefix' => '<div class="col-md-5 mb-3">',
      '#suffix' => '</div>',
    ];

    // Number of tests done
    $form['tests_done'] = [
      '#type' => 'number',
      '#title' => $this->t('No.of tests done'),
      '#title_display' => 'before',
      '#min' => 0,
      '#required' => TRUE,
      '#attributes' => ['class' => ['form-control', 'mb-3']],
      '#prefix' => '<div class="col-md-5 mb-3">',
      '#suffix' => '</div>',
    ];

    // Keywords
    $form['keywords'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Keywords'),
      '#title_display' => 'before',
      '#description' => $this->t('Separate keywords with commas'),
      '#required' => TRUE,
      '#attributes' => ['class' => ['form-control'], 'rows' => 2],
      '#prefix' => '<div class="col-md-5 mb-3">',
      '#suffix' => '</div> </div>',
    ];
// Submit button
    $form['actions'] = [
      '#type' => 'actions',
      '#prefix' => '<div class="col-md-12 text-center my-4">',
      '#suffix' => '</div>',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Product'),
      '#attributes' => ['class' => ['btn', 'btn-default']],
    ];

    // Attach library for styling
    $form['#attached']['library'][] = 'role_based_registration/product_form';

    return $form;
  }

  /**
   * Add row.
   */
  public function addPlatformSubmit(array &$form, FormStateInterface $form_state) {
    $platform_items = $form_state->get('platform_items');
    $new_delta = empty($platform_items) ? 0 : (max($platform_items) + 1);
    $platform_items[] = $new_delta;
    $form_state->set('platform_items', $platform_items);
    $form_state->setRebuild(TRUE);
  }

  /**
   * Remove row.
   */
  public function removePlatformSubmit(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $name = $trigger['#name']; // e.g. remove-2
    $delta = str_replace('remove-', '', $name);
    $platform_items = $form_state->get('platform_items');
    
    // Remove the item and reindex the array
    if (($key = array_search((int) $delta, $platform_items)) !== FALSE) {
      unset($platform_items[$key]);
      $platform_items = array_values($platform_items); // Reindex
    }
    
    $form_state->set('platform_items', $platform_items);
    $form_state->setRebuild(TRUE);
  }

  /**
   * Ajax callback.
   */
  public function ajaxCallback(array &$form, FormStateInterface $form_state) {
    return $form['platforms_wrapper'];
  }

  /**
   * Submit form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $user = \Drupal::currentUser();

    // Handle image
    $image = $form_state->getValue('image');
    $fid = NULL;
    if (!empty($image[0])) {
      $file = File::load($image[0]);
      $file->setPermanent();
      $file->save();
      $fid = $file->id();
    }
    // Collect values
  $keywords = $form_state->getValue('keywords');
  $platforms = $form_state->getValue('platforms');

    // Create node
    $node = Node::create([
      'type' => 'marchant_products',
      'title' => $form_state->getValue('title'),
    'body' => [
      'value' => $form_state->getValue('description'),
      'format' => 'plain_text',
    ],
    'field_legal_business_name' => $form_state->getValue('affiliated_link'),
    'field_sold_bys' => $form_state->getValue('sold_by'),
    'field_tests_done' => $form_state->getValue('tests_done'),
    'field_price' => $form_state->getValue('price'),
    'field_image' => $fid ? ['target_id' => $fid] : NULL,
    'field_rating' => $form_state->getValue('rating'),
    'field_prices' => [
      'value' => $keywords,
      'format' => 'plain_text',
    ],
    'uid' => $user->id(),
    'status' => 1,
    ]);

    // Save platforms as paragraphs
    $values = $form_state->getValue(['platforms_wrapper', 'platforms']);
    if (!empty($values)) {
      foreach ($values as $item) {
        // Only create paragraphs for items with data
        if (!empty($item['link']) || !empty($item['price']) || !empty($item['coupon'])) {
          $paragraph = Paragraph::create([
            'type' => 'platform_link',
            'field_platform_link' => $item['link'] ?? '',
            'field_platform_price' => $item['price'] ?? '',
            'field_coupon_code' => $item['coupon'] ?? '',
          ]);
          $paragraph->save();
          $node->get('field_platform_link')->appendItem($paragraph);
        }
      }
    }

    $node->save();
    $this->messenger()->addMessage($this->t('Product %title created successfully.', ['%title' => $node->label()]));
  }


 /**
   * Get user's primary role.
   */
  protected function getUserPrimaryRole(UserInterface $user) {
    $roles = $user->getRoles();
    $roles = array_diff($roles, ['authenticated']);
    return !empty($roles) ? reset($roles) : 'authenticated';
  }

}