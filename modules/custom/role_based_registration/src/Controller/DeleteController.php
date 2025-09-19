<?php


namespace Drupal\role_based_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Request;

class DeleteController extends ControllerBase {
  public function deleteNode($node) {
    $nid = $node->id();
    if ($node->access('delete')) {
      $node->delete();
      return new JsonResponse(['status' => 'success', 'nid' => $nid]);
    }
    return new JsonResponse(['status' => 'forbidden'], 403);
  }
}

// namespace Drupal\role_based_registration\Controller;

// use Drupal\Core\Controller\ControllerBase;
// use Symfony\Component\HttpFoundation\JsonResponse;
// use Drupal\node\Entity\Node;

// class DeleteController extends ControllerBase {
//   public function deleteNode($nid) {
//     $node = Node::load($nid);
//     if ($node) {
//       $node->delete();
//       return new JsonResponse(['status' => 'success']);
//     }
//     return new JsonResponse(['status' => 'error'], 400);
//   }
// }
