<?php

namespace Drupal\role_based_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\file\Entity\File;
use Drupal\Core\Url;

class ProductController extends ControllerBase {

  protected $fileSystem;

  public function __construct(FileSystemInterface $fileSystem) {
    $this->fileSystem = $fileSystem;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_system')
    );
  }

  /**
   * Extract product and save node.
   */
  public function extractProduct(Request $request) {
    $url = $request->query->get('url');

    if (empty($url)) {
      $this->messenger()->addError($this->t('No product URL provided.'));
      return $this->redirect('<front>');
    }

    try {
      $node = $this->extractProductData($url);

      $this->messenger()->addStatus($this->t('Product "%title" imported successfully!', [
        '%title' => $node->label(),
      ]));
      
      // Redirect to product form route
      $redirectUrl = Url::fromRoute('role_based_registration.product_form')->toString();
      return new RedirectResponse($redirectUrl);
    }
    catch (\Exception $e) {
      $this->messenger()->addError($this->t('Extraction failed: @msg', ['@msg' => $e->getMessage()]));
      return $this->redirect('<front>');
    }
  }

  private function extractProductData($url) {
    // Extract external_id
    preg_match('/\/dp\/([A-Z0-9]{10})/', $url, $matches);
    $external_id = $matches[1] ?? md5($url); // fallback for Flipkart

    if (empty($external_id)) {
      throw new \Exception('Could not extract product ID from the URL.');
    }

    // Check duplicate
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
      'type' => 'marchant_products',
      'field_external_id' => $external_id,
    ]);
    if (!empty($nodes)) {
      throw new \Exception('This product already exists in the system.');
    }

    // Validate domain
    if (!preg_match('/amazon\.in|flipkart\.com/', $url)) {
      throw new \Exception('Currently, only Amazon.in and Flipkart.com are supported.');
    }

    // Fetch page with proper headers to avoid 403 errors
    $client = \Drupal::httpClient();
    
    // Use realistic browser headers
    $headers = [
      'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
      'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
      'Accept-Language' => 'en-US,en;q=0.9',
      'Accept-Encoding' => 'gzip, deflate, br',
      'Connection' => 'keep-alive',
      'Upgrade-Insecure-Requests' => '1',
      'Sec-Fetch-Dest' => 'document',
      'Sec-Fetch-Mode' => 'navigate',
      'Sec-Fetch-Site' => 'none',
      'Sec-Fetch-User' => '?1',
      'Cache-Control' => 'max-age=0',
    ];

    // Add specific referer for each site
    if (strpos($url, 'amazon.in') !== FALSE) {
      $headers['Referer'] = 'https://www.amazon.in/';
    } else if (strpos($url, 'flipkart.com') !== FALSE) {
      $headers['Referer'] = 'https://www.flipkart.com/';
    }

    try {
      $response = $client->request('GET', $url, [
        'headers' => $headers,
        'allow_redirects' => [
          'max' => 5,
          'strict' => true,
          'referer' => true,
          'protocols' => ['http', 'https'],
        ],
        'timeout' => 30,
        // Note: verify should generally be true for security, but some sites may require false
        'verify' => true,
      ]);
      
      // Check if response is successful
      if ($response->getStatusCode() !== 200) {
        throw new \Exception('Failed to fetch product page. HTTP status: ' . $response->getStatusCode());
      }
      
      $html = (string) $response->getBody();

    } catch (\Exception $e) {
      throw new \Exception('Failed to fetch product page: ' . $e->getMessage());
    }

    // Check if HTML contains anti-bot measures
    if (strpos($html, 'captcha') !== false || strpos($html, 'robot') !== false || strpos($html, 'access denied') !== false) {
      throw new \Exception('Website blocked the request. Please try again later or use a different approach.');
    }

    $dom = new \DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new \DOMXPath($dom);

    $title = '';
    $price = 0;
    $description = '';
    $soldBy = '';
    $fid = NULL;

    if (strpos($url, 'amazon.in') !== FALSE) {
      // === Amazon selectors ===
      $titleNode = $xpath->query("//span[@id='productTitle']");
      $title = $titleNode->length ? trim($titleNode->item(0)->nodeValue) : 'Untitled Product';

      $priceNode = $xpath->query("//span[@class='a-price-whole']");
      $price = $priceNode->length ? preg_replace('/[^0-9.]/', '', $priceNode->item(0)->nodeValue) : 0;

      $descNode = $xpath->query("//div[@id='feature-bullets']//span[@class='a-list-item']");
      foreach ($descNode as $item) {
        $description .= trim($item->nodeValue) . "\n";
      }

      $sellerNode = $xpath->query("//a[@id='bylineInfo']");
      $soldBy = $sellerNode->length ? trim($sellerNode->item(0)->nodeValue) : 'Unknown Seller';

      $imageNode = $xpath->query("//img[@id='landingImage']");
      $imageUrl = $imageNode->length ? $imageNode->item(0)->getAttribute('src') : '';
    }
    else {
      // === Flipkart selectors ===
      $titleNode = $xpath->query("//span[@class='B_NuCI']");
      $title = $titleNode->length ? trim($titleNode->item(0)->nodeValue) : 'Untitled Product';

      $priceNode = $xpath->query("//div[@class='_30jeq3 _16Jk6d']");
      $price = $priceNode->length ? preg_replace('/[^0-9.]/', '', $priceNode->item(0)->nodeValue) : 0;

      $descNode = $xpath->query("//div[@class='_1mXcCf RmoJUa']//p");
      foreach ($descNode as $item) {
        $description .= trim($item->nodeValue) . "\n";
      }

      $sellerNode = $xpath->query("//div[@id='sellerName']//span//span");
      $soldBy = $sellerNode->length ? trim($sellerNode->item(0)->nodeValue) : 'Unknown Seller';

      $imageNode = $xpath->query("//img[@class='_396cs4 _2amPTt _3qGmMb  _3exPp9']");
      if ($imageNode->length === 0) {
        $imageNode = $xpath->query("//img[@class='_396cs4 _2amPTt _3qGmMb']");
      }
      $imageUrl = $imageNode->length ? $imageNode->item(0)->getAttribute('src') : '';
    }

    // Save image
    if (!empty($imageUrl)) {
      try {
        // Use Drupal's HTTP client for image download too
        $imageResponse = $client->request('GET', $imageUrl, [
          'headers' => [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Referer' => $url,
          ],
          'timeout' => 15,
        ]);
        
        if ($imageResponse->getStatusCode() === 200) {
          $imageData = (string) $imageResponse->getBody();
          $filename = 'product-' . $external_id . '-' . basename(parse_url($imageUrl, PHP_URL_PATH));
          
          $fileRepository = \Drupal::service('file.repository');
          $file = $fileRepository->writeData(
            $imageData,
            'public://product-images/' . $filename,
            FileSystemInterface::EXISTS_RENAME
          );
          
          if ($file) {
            $fid = $file->id();
          }
        }
      } catch (\Exception $e) {
        // Continue without image if download fails
        \Drupal::logger('role_based_registration')->warning('Failed to download product image: ' . $e->getMessage());
      }
    }

    // Create node
    $node = Node::create([
      'type'  => 'marchant_products',
      'title' => $title,
      'field_product_url' => $url,
      'field_price' => $price,
      'body'  => $description,
      'field_external_id' => $external_id,
      'field_sold_bys' => $soldBy,
      'field_image' => $fid ? ['target_id' => $fid] : NULL,
      'status' => 1,
    ]);
    $node->save();

    return $node;
  }
}