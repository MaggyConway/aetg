<?php

namespace Drupal\aetg_common\Plugin\WebformHandler;

use Drupal\file\Entity\File;
use Drupal\profile\Entity\Profile;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Webform submission remote post handler.
 *
 * @WebformHandler(
 *   id = "aetg_associated_profile",
 *   label = @Translation("AETG Associated profile"),
 *   category = @Translation("Custom"),
 *   description = @Translation("Create(edit) private profile."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 *   tokens = TRUE,
 * )
 */
class AetgAssociatedProfile extends WebformHandlerBase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->currentUser = $container->get('current_user');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    // Get an array of the values from the submission.
    $values = $webform_submission->getData();

    if (!empty(array_intersect($this->currentUser->getRoles(), ['asociado', 'escuela', 'personal_aetg']))) {
      return;
    }

    $profile_fields = [
      'field_nombre' => $values['full_name'],
      'field_apellidos' => $values['surnames'],
      'field_dni' => $values['dni_nie'],
      'field_num_socio_string' => $values['num_socio'],
      'field_imagen_usuario' => $values['image'],
      'field_lugar_nacimiento' => $values['birthplace'],
      'field_fecha_nacimiento' => $values['birthday'],
      'field_domicilio_particular' => [
        'country_code' => $values['address']['country_code'],
        'langcode' => $values['address']['country_code'],
        'address_line1' => $values['address']['address_line1'],
        'postal_code' => $values['address']['postal_code'],
        'locality' => $values['address']['locality'],
        'administrative_area' => $values['address']['administrative_area'],
      ],
      'field_email' => $values['email'],
      'field_telefono' => $values['phone'],
      'field_telefono_2' => $values['mobile'],
      'field_area_de_formacion' => $values['training_area'],
      'field_formacion_universitaria' => $values['university_training'],
      'field_formacion' => $values['non_university_training'],
      'field_centro_donde_trabaja' => $values['profession'],
      'field_iban_' => $values['iban'],
      'field_bic' => $values['bic'],
      'field_fecha_de_firma_de_la_domic' => $values['date_of_signature_of_the_direct_debit'],
      'field_domiciliacion_banco' => $values['bank_direct_debit'],
      'field_justificante_bancario' => $values['bank_receipt'],
    ];

    /** @var \Drupal\profile\ProfileStorageInterface $profile_storage */
    $profile_storage = $this->entityTypeManager->getStorage('profile');
    $private_profile = $profile_storage->loadByUser($this->currentUser, 'informacion_privada');

    if (empty($private_profile)) {
      $profile_fields += [
        'type' => 'informacion_privada',
        'langcode' => 'es',
        'uid' => $this->currentUser->id(),
      ];

      $private_profile = Profile::create($profile_fields);
      $private_profile->save();
    }
    elseif ($private_profile instanceof \Drupal\profile\Entity\ProfileInterface) {
      foreach ($profile_fields as $field_name => $val) {
        $private_profile->set($field_name, $val);
      }
      $private_profile->save();
    }
  }

}
