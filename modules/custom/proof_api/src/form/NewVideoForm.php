<?php

/**
 * @file
 * Contains \Drupal\proof_api\ProofAPIForms\ProofAPIForms.
 */

namespace Drupal\proof_api\ProofAPIForms;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\proof_api\ProofAPIRequests\ProofAPIRequests;
use Drupal\proof_api\ProofAPIUtilities\ProofAPIUtilities;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create NewVideoForm form.
 */
class NewVideoForm extends FormBase
{
  private $proofAPIRequests;
  private $proofAPIUtilities;

  /**
   * NewVideoForm constructor.
   * @param ProofAPIRequests $proofAPIRequests
   * @param ProofAPIUtilities $proofAPIUtilities
   */
  public function __construct(ProofAPIRequests $proofAPIRequests, ProofAPIUtilities $proofAPIUtilities)
  {
      $this->proofAPIRequests = $proofAPIRequests;
      $this->proofAPIUtilities = $proofAPIUtilities;
  }

  /**
   * @return string
   */
  public function getFormId()
  {
    return 'proof_api_new_video_form';
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   * @return array
   */
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

  /**
   * Validates user input along several parameters and returns error response, if necessary:
   * - 1) Assures that a url has been entered in a proper url format
   * - 2) Assures that the submitted video does not match an existing video:
   *  - by getting all video through the ProofAPIRequests service, then
   *  - assuring that the url does not match an existing url, and
   *  - assuring that the slug does not match an existing slug
   *  - NOTE: Some videos could still slip through if they are hosted by different domains AND they are assigned different slugs
   * - 3) Assures that the slug is properly formatted in lowercase
   * - 4) Assures that the video is hosted by a source that this module is able to embed.
   *    - This module currently supports embedding on videos from:
   *      - Youtube
   *      - Vimeo
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $url = $form_state->getValue('url');
    $slug = $form_state->getValue('slug');
    $response = $this->proofAPIRequests->getAllVideos();
    $slugNoDashes = str_replace('-', '', $slug);
    $slugLowercase = ctype_lower($slugNoDashes);

    $videosMatch = $this->proofAPIUtilities->videosMatch($url, $slug, $response);
    $videoOrigin = $this->proofAPIUtilities->checkVideoOrigin($url);

    if (!UrlHelper::isValid($url, TRUE)) {
        $form_state->setErrorByName('url', t('Sorry, the video url is invalid.'));
    } else if ($videosMatch) {
        $form_state->setErrorByName('title', t('Sorry, this appears to be a duplicate video entry.'));
    } else if (!$slugLowercase) {
        $form_state->setErrorByName('slug', t('Sorry, the slug appears to be in the wrong format.'));
    } else if ($videoOrigin === null) {
        $form_state->setErrorByName('url', t('Sorry, this app only supports YouTube and Vimeo videos at this time.'));
    } else if (date('N') > 5) {
      $form_state->setErrorByName('title', t('Sorry, you cannot post videos on weekends.'));
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

    $form_state->setRedirect('proof_api.home');
    return;
  }

  /**
   * @param ContainerInterface $container
   * @return static
   */
  public static function create(ContainerInterface $container)
  {
    $proofAPIRequests = $container->get('proof_api.proof_api_requests');
    $proofAPIUtilities = $container->get('proof_api.proof_api_utilities');

    return new static($proofAPIRequests, $proofAPIUtilities);
  }
}
