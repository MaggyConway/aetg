<?php

namespace Drupal\aetg_common\Plugin\RulesAction;

use Drupal\Core\Language\LanguageInterface;
use Drupal\rules\Plugin\RulesAction\SystemSendEmail;

/**
 * Provides "AETG Send email" rules action.
 *
 * @RulesAction(
 *   id = "rules_aetg_send_email",
 *   label = @Translation("AETG Send email"),
 *   category = @Translation("System"),
 *   context_definitions = {
 *     "to" = @ContextDefinition("email",
 *       label = @Translation("Send to"),
 *       description = @Translation("Email address(es) drupal will send an email to."),
 *       multiple = TRUE
 *     ),
 *     "subject" = @ContextDefinition("string",
 *       label = @Translation("Subject"),
 *       description = @Translation("The email's subject.")
 *     ),
 *     "message" = @ContextDefinition("text",
 *       label = @Translation("Message"),
 *       description = @Translation("The email's message body. Drupal will by default remove all HTML tags. If you want to use HTML you must override this behavior by installing a contributed module such as Mime Mail.")
 *     ),
 *     "reply" = @ContextDefinition("email",
 *       label = @Translation("Reply to"),
 *       description = @Translation("The email's reply-to address. Leave it empty to use the site-wide configured address."),
 *       default_value = NULL,
 *       required = FALSE
 *     ),
 *     "language" = @ContextDefinition("language",
 *       label = @Translation("Language"),
 *       description = @Translation("If specified, the language used for getting the email message and subject."),
 *       default_value = NULL,
 *       required = FALSE
 *     ),
 *     "key" = @ContextDefinition("string",
 *       label = @Translation("Key"),
 *       description = @Translation("The email's key."),
 *       default_value = "rules_aetg_send_email"
 *     ),
 *   }
 * )
 *
 * @todo Define that message Context should be textarea comparing with textfield Subject
 * @todo Add access callback information from Drupal 7.
 */
class AetgSystemSendEmail extends SystemSendEmail {

  /**
   * Send a system email.
   *
   * @param string[] $to
   *   Email addresses of the recipients.
   * @param string $subject
   *   Subject of the email.
   * @param string $message
   *   Email message text.
   * @param string|null $reply
   *   (optional) Reply to email address.
   * @param \Drupal\Core\Language\LanguageInterface|null $language
   *   (optional) Language code.
   * @param string|null $key
   *   Key of the email.
   */
  protected function doExecute(array $to, $subject, $message, $reply = NULL, LanguageInterface $language = NULL, $key = NULL) {
    $langcode = isset($language) ? $language->getId() : LanguageInterface::LANGCODE_SITE_DEFAULT;
    $params = [
      'subject' => $subject,
      'message' => $this->t($message),
    ];
    // Set a unique key for this email.
    $key = $key ?? $this->getPluginId();
    $key = 'rules_action_mail_' . $key;

    $recipients = implode(', ', $to);
    $message = $this->mailManager->mail('rules', $key, $recipients, $langcode, $params, $reply);
    if ($message['result']) {
      $this->logger->notice('Successfully sent email to %recipient', ['%recipient' => $recipients]);
    }

  }

}
