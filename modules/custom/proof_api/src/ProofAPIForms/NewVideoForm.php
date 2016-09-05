<?php

namespace Drupal\proof_api\ProofAPIForms;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\proof_api\ProofAPIRequests\ProofAPIRequests;
use Drupal\proof_api\ProofAPIUtilities\ProofAPIUtilities;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NewVideoForm extends FormBase
{
  private $proofAPIRequests;
  private $proofAPIUtilities;

  public function __construct(ProofAPIRequests $proofAPIRequests, ProofAPIUtilities $proofAPIUtilities)
  {
      $this->proofAPIRequests = $proofAPIRequests;
      $this->proofAPIUtilities = $proofAPIUtilities;
  }

  public function getFormId()
  {
    return 'proof_api_new_video_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => t('Video Title'),
      '#required' => TRUE,
    );

    $form['url'] = array(
      '#type' => 'url',
      '#title' => t('Video URL'),
      '#required' => TRUE,
      );

    $form['slug'] = array(
      '#type' => 'textfield',
      '#title' => t('Video Slug'),
      '#required' => TRUE,
      '#attributes' => array(
        'placeholder' => t('name-in-this-format'),
      ),
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Submit'),
    );

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $url = $form_state->getValue('url');
    $slug = $form_state->getValue('slug');
    $response = $this->proofAPIRequests->listAllVideos();
    $slugNoDashes = str_replace('-', '', $slug);
    $slugLowercase = ctype_lower($slugNoDashes);

    $videosMatch = $this->proofAPIUtilities->videosMatch($url, $slug, $response);

    if (!UrlHelper::isValid($url, TRUE)) {
      $form_state->setErrorByName('url', t('Sorry, the video url is invalid.'));
    } else if ($videosMatch) {
        $form_state->setErrorByName('title', t('Sorry, this appears to be a duplicate video entry.'));
    } else if (!$slugLowercase) {
      $form_state->setErrorByName('slug', t('Sorry, the slug appears to be in the wrong format.'));
    }
  }


  /**
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $title = $form_state->getValue('title');
    $url = $form_state->getValue('url');
    $slug = $form_state->getValue('slug');

    $this->proofAPIRequests->postNewMovie($title, $url, $slug);

    $form_state->setRedirect('proof_api.all_videos');
    return;
  }

  public static function create(ContainerInterface $container)
  {
    $proofAPIRequests = $container->get('proof_api.proof_api_requests');
    $proofAPIUtilities = $container->get('proof_api.proof_api_utilities');

    return new static($proofAPIRequests, $proofAPIUtilities);
  }
}
