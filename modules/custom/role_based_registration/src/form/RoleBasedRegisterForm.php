<?php

namespace Drupal\role_based_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

class RoleBasedRegisterForm extends FormBase {

  public function getFormId() {
    return 'role_based_register_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    // Initialize counters for dynamic entries
    $counters = [
      'num_platforms', 
      'num_products',
      'num_socials',
      'num_tester_platforms'
    ];
    
    foreach ($counters as $counter) {
      if (!$form_state->has($counter)) {
        $form_state->set($counter, 1);
      }
    }

    $role = $form_state->getValue('role', '');

    // Role select with AJAX
    $form['role'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Role'),
      '#options' => [
        '' => $this->t('- Select Role -'),
        'authenticated' => $this->t('Authenticated user'),
        'merchant' => $this->t('Merchant'),
        'gamechanger' => $this->t('Game changer'),
        // 'tester' => $this->t('Tester'),
        
        
      ],
      '#ajax' => [
        'callback' => '::updateRoleFields',
        'wrapper' => 'role-fields-wrapper',
        
      ],
      
      '#required' => TRUE,
      '#default_value' => $role,
    ];

    // Role-specific fields container
    $form['role_fields'] = [
      '#type' => 'container',
      '#prefix' => '<div id="role-fields-wrapper" class="row">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
    ];

    if ($role) {
      // User details fieldset
      $form['role_fields']['user_details'] = [
        '#type' => 'fieldset',
        // '#title' => $this->t('User Details'),
      ];
      
      $form['role_fields']['user_details']['first_name'] = [
        '#type' => 'textfield',
        '#placeholder' => $this->t('First Name'),
        '#required' => TRUE,
        '#prefix' => '<div class="col-md-6">',
        '#suffix' => '</div>',
        '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
      ];

      
      $form['role_fields']['user_details']['last_name'] = [
        '#type' => 'textfield',
        '#placeholder' => $this->t('Last Name'),
        '#required' => TRUE,
          '#prefix' => '<div class="col-md-6">',
        '#suffix' => '</div>',
        '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
      ];
      
      $form['role_fields']['user_details']['email'] = [
        '#type' => 'email',
        '#placeholder' => $this->t('Email'),
        '#required' => TRUE,
        '#prefix' => '<div class="col-md-6">',
        '#suffix' => '</div>',
        '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
      ];
      
      $form['role_fields']['user_details']['password'] = [
        '#type' => 'password_confirm',
        '#placeholder' => $this->t('Password'),
        '#required' => TRUE,
        '#prefix' => '<div class="col-md-6">',
        '#suffix' => '</div>',
        '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
      ];
      $form['role_fields']['user_details']['phone_number'] = [
        '#type' => 'tel',
        '#placeholder' => $this->t('Phone Number'),
        '#required' => TRUE,
        '#prefix' => '<div class="col-md-6">',
        '#suffix' => '</div>',
        '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
      ];


      // Role-specific fields
      switch ($role) {
        case 'merchant':
          $this->buildMerchantFields($form, $form_state);
          break;

        case 'tester':
          $form['role_fields']['test_code'] = [
            '#type' => 'textfield',
            '#placeholder' => $this->t('Tester Code'),
            '#required' => TRUE,
            '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
          ];
          
          // Dynamic tester platform details
          $n = $form_state->get('num_tester_platforms');
          $form['role_fields']['tester_platforms'] = [
            '#type' => 'fieldset',
            '#placeholder' => $this->t('Platform Details'),
            '#tree' => TRUE,
            '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
          ];
          for ($i = 0; $i < $n; $i++) {
            $form['role_fields']['tester_platforms'][$i] = [
              'platform_name' => [
                '#type' => 'textfield',
                '#placeholder' => $this->t('Platform Name'),
                '#required' => TRUE,
                '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
              ],
              'username' => [
                '#type' => 'textfield',
                '#placeholder' => $this->t('Username / Handle'),
                '#required' => TRUE,
                '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
              ],
              'followers' => [
                '#type' => 'number',
                '#placeholder' => $this->t('Number of Followers'),
                '#required' => TRUE,
                '#min' => 0,
                '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
              ],
            ];
          }
          break;

        case 'gamechanger':
          $form['role_fields']['innovation_area'] = [
            '#type' => 'textfield',
            '#placeholder' => $this->t('Area of Innovation'),
            '#required' => TRUE,
            '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
          ];
          break;
      }

      // Submit button
      $form['role_fields']['actions'] = [
        '#type' => 'actions',
      ];
      $form['role_fields']['actions']['submit'] = [
        '#type' => 'submit',  
        '#value' => $this->t('Register'),
        '#button_type' => 'primary',
      ];
    }

    return $form;
  }

  public function updateRoleFields(array &$form, FormStateInterface $form_state) {
    return $form['role_fields'];
  }

  private function buildMerchantFields(array &$form, FormStateInterface $form_state) {
    $form['role_fields']['business_name'] = [
      '#type' => 'textfield',
      '#placeholder' => $this->t('Business Name'),
      '#required' => TRUE,
      '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
    ];
    
    $form['role_fields']['brand_name'] = [
      '#type' => 'textfield',
      '#placeholder' => $this->t('Brand Name'),
      '#required' => TRUE,
      '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
    ];

    // Platforms list
    $n = $form_state->get('num_platforms');
    $form['role_fields']['online_platforms'] = [
      '#type' => 'fieldset',
      '#placeholder' => $this->t('Online Selling Platforms'),
      '#tree' => TRUE,
      '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
    ];
    for ($i = 0; $i < $n; $i++) {
      $form['role_fields']['online_platforms'][$i] = [
        'platform_name' => [
          '#type' => 'textfield',
          '#placeholder' => $this->t('Platform Name'),
          '#required' => TRUE,
          '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
        ],
        'store_link' => [
          '#type' => 'url',
          '#placeholder' => $this->t('Store Link'),
          '#required' => TRUE,
          '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
        ],
      ];
    }

    // Products list
    $m = $form_state->get('num_products');
    $form['role_fields']['product_list'] = [
      '#type' => 'fieldset',
      '#placeholder' => $this->t('Product List with Buying Links'),
      '#tree' => TRUE,
      '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
    ];
    for ($i = 0; $i < $m; $i++) {
      $form['role_fields']['product_list'][$i] = [
        'product_name' => [
          '#type' => 'textfield',
          '#placeholder' => $this->t('Product Name'),
          '#required' => TRUE,
          '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
        ],
        'buying_link' => [
          '#type' => 'url',
          '#placeholder' => $this->t('Buying Link'),
          '#required' => TRUE,
          '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
        ],
      ];
    }

    // Social media handles
    $s = $form_state->get('num_socials');
    $form['role_fields']['social_media'] = [
      '#type' => 'fieldset',
      '#placeholder' => $this->t('Social Media Handles'),
      '#tree' => TRUE,
      '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
    ];
    for ($i = 0; $i < $s; $i++) {
      $form['role_fields']['social_media'][$i] = [
        'platform' => [
          '#type' => 'textfield',
          '#placeholder' => $this->t('Platform Name'),
          '#required' => TRUE,
          '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
        ],
        'profile_link' => [
          '#type' => 'url',
          '#placeholder' => $this->t('Page/Profile Link'),
          '#required' => TRUE,
          '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
        ],
      ];
    }

    $form['role_fields']['business_email'] = [
      '#type' => 'email',
      '#placeholder' => $this->t('Business Email'),
      '#required' => TRUE,
      '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
    ];
    
    $form['role_fields']['business_phone'] = [
      '#type' => 'tel',
      '#placeholder' => $this->t('Business Phone Number'),
      '#required' => TRUE,
      '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
    ];
    
    $form['role_fields']['business_address'] = [
      '#type' => 'textarea',
      '#placeholder' => $this->t('Business Address'),
      '#required' => TRUE,
      '#attributes' => [
          'class' => ['form-control mb-3'], 
        ],
    ];
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $role = $form_state->getValue('role');
    
    if ($role) {
      $user_details = $form_state->getValue(['role_fields', 'user_details']);
      
      // Validate email
      if (!empty($user_details['email'])) {
        if (!\Drupal::service('email.validator')->isValid($user_details['email'])) {
          $form_state->setErrorByName('role_fields][user_details][email', $this->t('Please enter a valid email address.'));
        }
        elseif (user_load_by_mail($user_details['email'])) {
          $form_state->setErrorByName('role_fields][user_details][email', $this->t('This email is already registered.'));
        }
      }
      
      // Validate first name and last name
      if (empty(trim($user_details['first_name']))) {
        $form_state->setErrorByName('role_fields][user_details][first_name', $this->t('First name is required.'));
      }
      
      if (empty(trim($user_details['last_name']))) {
        $form_state->setErrorByName('role_fields][user_details][last_name', $this->t('Last name is required.'));
      }
      
      // Validate password
      if (empty($user_details['password'])) {
        $form_state->setErrorByName('role_fields][user_details][password', $this->t('Password is required.'));
      }
      
      // Validate phone number
      if (empty(trim($user_details['phone_number']))) {
        $form_state->setErrorByName('role_fields][user_details][phone_number', $this->t('Phone number is required.'));
      }

      
      // Role-specific validations
      if ($role === 'tester') {
        $test_code = $form_state->getValue(['role_fields', 'test_code']);
        if ($test_code !== 'SECRET123') {
          $form_state->setErrorByName('role_fields][test_code', $this->t('Invalid tester code.'));
        }
      }
      
      // Validate merchant fields
      if ($role === 'merchant') {
        $business_email = $form_state->getValue(['role_fields', 'business_email']);
        if (!empty($business_email)) {
          if (!\Drupal::service('email.validator')->isValid($business_email)) {
            $form_state->setErrorByName('role_fields][business_email', $this->t('Please enter a valid business email address.'));
          }
        }
      }
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $role = $form_state->getValue('role');
    $user_details = $form_state->getValue(['role_fields', 'user_details']);
    
    // Generate username from first name and last name
    $base_username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', 
      $user_details['first_name'] . '.' . $user_details['last_name']
    ));
    $username = $base_username;
    $counter = 1;
    
    // Ensure username is unique
    while (user_load_by_name($username)) {
      $username = $base_username . $counter;
      $counter++;
    }
    
    // Create user
    $user = User::create([
      'name' => $username,
      'mail' => $user_details['email'],
      // 'pass' => $user_details['password'],
      'status' => 1,
      'field_first_name' => $user_details['first_name'],
      'field_last_name' => $user_details['last_name'],
      'field_phone_number' => $user_details['phone_number'],
    ]);
    // Set the password properly
if (!empty($user_details['password'])) {
  $user->setPassword($user_details['password']);
}
    // Add role
    if ($role !== 'authenticated') {
      $user->addRole($role);
    }
    
    // Save role-specific fields
    $this->saveRoleSpecificFields($user, $form_state);
    
    // Save user
    $user->save();
    
    // Set message and redirect
    \Drupal::messenger()->addStatus($this->t('Registration successful. You may now log in.'));
    $form_state->setRedirect('user.login');
  }
  
  protected function saveRoleSpecificFields(User $user, FormStateInterface $form_state) {
    $role = $form_state->getValue('role');
    $values = $form_state->getValue('role_fields');
    
    switch ($role) {
      case 'merchant':
        $user->set('field_business_name', $values['business_name']);
        $user->set('field_brand_name', $values['brand_name']);
        $user->set('field_business_email', $values['business_email']);
        $user->set('field_business_phone_number', $values['business_phone']);
        $user->set('field_business_address', $values['business_address']);
        
        // Save platforms - field_platform_name (text field)
        $platform_names = [];
        foreach ($values['online_platforms'] as $platform) {
            if (!empty($platform['platform_name'])) {
                $platform_names[] = $platform['platform_name'];
            }
        }
        $user->set('field_platform_name', $platform_names);
        
        // Save store links - field_store_link (link field)
        $store_links = [];
        foreach ($values['online_platforms'] as $platform) {
            if (!empty($platform['store_link'])) {
                // Ensure URL has proper scheme
                $url = $platform['store_link'];
                if (!parse_url($url, PHP_URL_SCHEME)) {
                    $url = 'https://' . $url;
                }
                
                $store_links[] = [
                    'uri' => $url,
                    'title' => $platform['platform_name'] ?? '',
                    'options' => [],
                ];
            }
        }    
        $user->set('field_store_link', $store_links);
        
        // Save products - field_product_name (text field)
        $product_names = [];
        foreach ($values['product_list'] as $product) {
            if (!empty($product['product_name'])) {
                $product_names[] = $product['product_name'];
            }
        }
        $user->set('field_product_name', $product_names);
        
        // Save buying links - field_buying_link (link field)
        $buying_links = [];
        foreach ($values['product_list'] as $product) {
            if (!empty($product['buying_link'])) {
                // Ensure URL has proper scheme
                $url = $product['buying_link'];
                if (!parse_url($url, PHP_URL_SCHEME)) {
                    $url = 'https://' . $url;
                }
                
                $buying_links[] = [
                    'uri' => $url,
                    'title' => $product['product_name'] ?? '',
                    'options' => [],
                ];
            }
        }
        $user->set('field_buying_link', $buying_links);
        
        // Save social media platforms - field_social_platform (text field)
        $social_platforms = [];
        foreach ($values['social_media'] as $social) {
            if (!empty($social['platform'])) {
                $social_platforms[] = $social['platform'];
            }
        }
        $user->set('field_social_platform', $social_platforms);
        
        // Save social media profile links - field_social_profile_link (link field)
        $social_links = [];
        foreach ($values['social_media'] as $social) {
            if (!empty($social['profile_link'])) {
                // Ensure URL has proper scheme
                $url = $social['profile_link'];
                if (!parse_url($url, PHP_URL_SCHEME)) {
                    $url = 'https://' . $url;
                }
                
                $social_links[] = [
                    'uri' => $url,
                    'title' => $social['platform'] ?? '',
                    'options' => [],
                ];
            }
        }
        $user->set('field_social_profile_link', $social_links);
        break;
        
      case 'tester':
        $user->set('field_tester_code', $values['test_code']);
        
        // Save tester platforms
        $platforms = [];
        foreach ($values['tester_platforms'] as $platform) {
          if (!empty($platform['platform_name'])) {
            $platforms[] = [
              'platform_name' => $platform['platform_name'],
              'username' => $platform['username'],
              'followers' => $platform['followers'],
            ];
          }
        }
        $user->set('field_tester_platforms', $platforms);
        break;
        
      case 'gamechanger':
        $user->set('field_innovation_area', $values['innovation_area']);
        break;
    }
  }
}
